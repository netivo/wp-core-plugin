<?php
/**
 * Created by Netivo for Netivo Core Plugin.
 * User: Michal
 * Date: 23.10.2018
 * Time: 15:14
 *
 * @package Netivo\Core
 */

namespace Netivo\Core;

if ( ! defined( 'ABSPATH' ) ) {
	header( 'HTTP/1.0 403 Forbidden' );
	exit;
}

if ( ! class_exists( '\Netivo\Core\Annotations' ) ) {
	/**
	 * Class Annotations
	 */
	class Annotations {
		/**
		 * Static array to store already parsed annotations
		 *
		 * @var array
		 */
		protected static $annotationCache;
		
		
		/**
		 * Gets all anotations with pattern @SomeAnnotation() from a given class.
		 *
		 * @param  string $className class name to get annotations.
		 *
		 * @return array  self::$annotationCache all annotated elements.
		 *
		 * @throws \ReflectionException When error.
		 */
		public static function get_class_annotations( $className ) {
			if ( ! isset( self::$annotationCache[ $className ] ) ) {
				$class                               = new \ReflectionClass( $className );
				self::$annotationCache[ $className ] = self::parse_annotations( $class->getDocComment() );
			}
			
			return self::$annotationCache[ $className ];
		}
		
		/**
		 * Gets all anotations with pattern @SomeAnnotation() from a determinated method of a given class
		 *
		 * @param  string $className class name.
		 * @param  string $methodName method name to get annotations.
		 *
		 * @return array  self::$annotationCache all annotated elements of a method given
		 */
		public static function get_method_annotations( $className, $methodName ) {
			if ( ! isset( self::$annotationCache[ $className . '::' . $methodName ] ) ) {
				try {
					$method      = new \ReflectionMethod( $className, $methodName );
					$annotations = self::parse_annotations( $method->getDocComment() );
				} catch ( \ReflectionException $e ) {
					$annotations = array();
				}
				
				self::$annotationCache[ $className . '::' . $methodName ] = $annotations;
			}
			
			return self::$annotationCache[ $className . '::' . $methodName ];
		}
		
		/**
		 * Gets all anotations with pattern @SomeAnnotation() from a determinated method of a given class
		 *
		 * @param  string $className class name.
		 * @param  string $propertyName property name to get annotations.
		 *
		 * @return array  self::$annotationCache all annotated elements of a method given
		 */
		public static function get_property_annotations( $className, $propertyName ) {
			if ( ! isset( self::$annotationCache[ $className . '->' . $propertyName ] ) ) {
				try {
					$method      = new \ReflectionProperty( $className, $propertyName );
					$annotations = self::parse_annotations( $method->getDocComment() );
				} catch ( \ReflectionException $e ) {
					$annotations = array();
				}
				
				self::$annotationCache[ $className . '->' . $propertyName ] = $annotations;
			}
			
			return self::$annotationCache[ $className . '->' . $propertyName ];
		}
		
		
		/**
		 * Parse annotations
		 *
		 * @param  string $docblock dockblok.
		 *
		 * @return array parsed annotations params
		 */
		protected static function parse_annotations( $docblock ) {
			$annotations = array();
			
			$docblock = substr( $docblock, 3, - 2 );
			
			if ( preg_match_all( '/@(?<name>[A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/m', $docblock, $matches ) ) {
				$numMatches = count( $matches[0] );
				
				for ( $i = 0; $i < $numMatches; ++ $i ) {
					if ( isset( $matches['args'][ $i ] ) ) {
						$argsParts = trim( $matches['args'][ $i ] );
						$name      = $matches['name'][ $i ];
						$value     = self::parse_args( $argsParts );
					} else {
						$value = array();
					}
					
					$annotations[ $name ][] = $value;
				}
				
			}
			
			return $annotations;
		}
		
		/**
		 * Parse individual annotation arguments
		 *
		 * @param  string $content arguments string.
		 *
		 * @return array           annotated arguments
		 *
		 * @throws \InvalidArgumentException When error.
		 */
		protected static function parse_args( $content ) {
			$data  = array();
			$len   = strlen( $content );
			$i     = 0;
			$var   = '';
			$val   = '';
			$level = 1;
			
			$prevDelimiter = '';
			$nextDelimiter = '';
			$nextToken     = '';
			$composing     = false;
			$type          = 'plain';
			$delimiter     = null;
			$quoted        = false;
			$tokens        = array( '"', '"', '{', '}', ',', '=' );
			
			while ( $i <= $len ) {
				$c = substr( $content, $i ++, 1 );
				
				if ( $c === '\'' || $c === '"' ) {
					$delimiter = $c;
					if ( ! $composing && empty( $prevDelimiter ) && empty( $nextDelimiter ) ) {
						$prevDelimiter = $nextDelimiter = $delimiter;
						$val           = '';
						$composing     = true;
						$quoted        = true;
					} else {
						if ( $c !== $nextDelimiter ) {
							throw new \InvalidArgumentException( sprintf(
								"Parse Error: enclosing error -> expected: [%s], given: [%s]",
								$nextDelimiter, $c
							) );
						}
						
						if ( $i < $len ) {
							if ( ',' !== substr( $content, $i, 1 ) ) {
								throw new \InvalidArgumentException( sprintf(
									"Parse Error: missing comma separator near: ...%s<--",
									substr( $content, ( $i - 10 ), $i )
								) );
							}
						}
						
						$prevDelimiter = $nextDelimiter = '';
						$composing     = false;
						$delimiter     = null;
					}
				} elseif ( ! $composing && in_array( $c, $tokens ) ) {
					switch ( $c ) {
						case '=':
							$prevDelimiter = $nextDelimiter = '';
							$level         = 2;
							$composing     = false;
							$type          = 'assoc';
							$quoted        = false;
							break;
						case ',':
							$level = 3;
							
							// If composing flag is true yet,
							// it means that the string was not enclosed, so it is parsing error.
							if ( $composing === true && ! empty( $prevDelimiter ) && ! empty( $nextDelimiter ) ) {
								throw new \InvalidArgumentException( sprintf(
									"Parse Error: enclosing error -> expected: [%s], given: [%s]",
									$nextDelimiter, $c
								) );
							}
							
							$prevDelimiter = $nextDelimiter = '';
							break;
						case '{':
							$subc         = '';
							$subComposing = true;
							
							while ( $i <= $len ) {
								$c = substr( $content, $i ++, 1 );
								
								if ( isset( $delimiter ) && $c === $delimiter ) {
									throw new \InvalidArgumentException( sprintf(
										"Parse Error: Composite variable is not enclosed correctly."
									) );
								}
								
								if ( $c === '}' ) {
									$subComposing = false;
									break;
								}
								$subc .= $c;
							}
							
							if ( $subComposing ) {
								throw new \InvalidArgumentException( sprintf(
									"Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
									$subc
								) );
							}
							
							$val = self::parse_args( $subc );
							break;
					}
				} else {
					if ( $level == 1 ) {
						$var .= $c;
					} elseif ( $level == 2 ) {
						$val .= $c;
					}
				}
				
				if ( $level === 3 || $i === $len ) {
					if ( $type == 'plain' && $i === $len ) {
						$data = self::cast_value( $var );
					} else {
						$data[ trim( $var ) ] = self::cast_value( $val, ! $quoted );
					}
					
					$level     = 1;
					$var       = $val = '';
					$composing = false;
					$quoted    = false;
				}
			}
			
			return $data;
		}
		
		/**
		 * Try determinate the original type variable of a string
		 *
		 * @param  string  $val string containing possibles variables that can be cast to bool or int.
		 * @param  boolean $trim indicate if the value passed should be trimmed after to try cast.
		 *
		 * @return mixed         returns the value converted to original type if was possible
		 */
		protected static function cast_value( $val, $trim = false ) {
			if ( is_array( $val ) ) {
				foreach ( $val as $key => $value ) {
					$val[ $key ] = self::cast_value( $value );
				}
			} elseif ( is_string( $val ) ) {
				if ( $trim ) {
					$val = trim( $val );
				}
				
				$tmp = strtolower( $val );
				
				if ( $tmp === 'false' || $tmp === 'true' ) {
					$val = $tmp === 'true';
				} elseif ( is_numeric( $val ) ) {
					return $val + 0;
				}
				
				unset( $tmp );
			}
			
			return $val;
		}
	}
}


<?php

namespace MPHB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


abstract class AbstractDataTransferObject {


	public function toArray() {

		$result = array();

		$reflectionClass = new \ReflectionClass( get_class( $this ) );

		foreach ( $reflectionClass->getProperties() as $property ) {

			if ( $property->isPrivate() ) {

				$property->setAccessible( true );

				$result[ $property->getName() ] = $property->getValue( $this );

				$property->setAccessible( false );

			} else {
				$propertyName            = $property->getName();
				$result[ $propertyName ] = $this->$propertyName;
			}
		}
		return $result;
	}
}

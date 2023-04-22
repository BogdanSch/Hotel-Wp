<?php

namespace MPHB;

class Autoloader {

	const CLASSES_NAMESPACE_PREFIX = 'MPHB\\';

	/**
	 *
	 * @var int
	 */
	private $prefixLength;

	/**
	 *
	 * @var string
	 */
	private $basePath;

	/**
	 *
	 * @var string
	 */
	private $classDirSeparator;
	private $customPathList = array();

	/**
	 * @param string $basePath Path to plugin directory
	 */
	public function __construct( $basePath ) {

		$this->prefixLength = strlen( static::CLASSES_NAMESPACE_PREFIX );
		$this->basePath     = $basePath;

		$this->classDirSeparator = '\\'; // namespaces

		$this->setupCustomPathList();

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	private function setupCustomPathList() {

		$this->customPathList['Libraries\\WP_SessionManager\\Recursive_ArrayAccess'] = 'includes/libraries/wp-session-manager/class-recursive-arrayaccess.php';
		$this->customPathList['Libraries\\WP_SessionManager\\WP_Session']            = 'includes/libraries/wp-session-manager/class-wp-session.php';
		$this->customPathList['Libraries\\EDD_Plugin_Updater\\EDD_Plugin_Updater']   = 'includes/libraries/edd-plugin-updater/edd-plugin-updater.php';

		$this->customPathList['Core\\CoreAPI'] = 'includes/core/core-api.php';
		$this->customPathList['Core\\RoomTypeAvailabilityStatus'] = 'includes/core/data/room-type-availability-status.php';
		$this->customPathList['Core\\AbstractDataTransferObject'] = 'includes/core/data/abstract-data-transfer-object.php';
		$this->customPathList['Core\\RoomTypeAvailabilityData'] = 'includes/core/data/room-type-availability-data.php';
		$this->customPathList['Core\\RoomAvailabilityHelper'] = 'includes/core/helpers/room-availability-helper.php';

		$this->customPathList['AjaxApi\\AbstractAjaxApiAction']   = 'includes/ajax-api/ajax-actions/abstract-ajax-api-action.php';
		$this->customPathList['AjaxApi\\GetRoomTypeCalendarData'] = 'includes/ajax-api/ajax-actions/get-room-type-calendar-data.php';
		$this->customPathList['AjaxApi\\GetRoomTypeAvailabilityData'] = 'includes/ajax-api/ajax-actions/get-room-type-availability-data.php';
		$this->customPathList['AjaxApi\\GetAdminCalendarBookingInfo'] = 'includes/ajax-api/ajax-actions/get-admin-calendar-booking-info.php';

	}

	/**
	 * @param string $class
	 */
	public function autoload( $class ) {

		$class = ltrim( $class, '\\' );

		// does the class use the namespace prefix?
		if ( strncmp( static::CLASSES_NAMESPACE_PREFIX, $class, $this->prefixLength ) !== 0 ) {
			// no, move to the next registered autoloader
			return false;
		}

		$relativeClass = substr( $class, $this->prefixLength );

		// replace the namespace prefix with the base directory, replace namespace
		// separators with directory separators in the relative class name, append
		// with .php
		$file = $this->convertClassToPath( $relativeClass );

		// if the file exists, require it
		if ( file_exists( $file ) ) {

			require_once $file;
			return $file;
		}
		return false;
	}

	/**
	 *
	 * @param string $class
	 * @return string Relative path to classfile.
	 */
	private function convertClassToPath( $class ) {

		$path = '';

		if ( array_key_exists( $class, $this->customPathList ) ) {

			$path = $this->basePath . $this->customPathList[ $class ];

		} else {

			$path = $this->basePath . 'includes/' . $this->defaultConvert( $class );
		}

		return $path;
	}

	private function defaultConvert( $class ) {

		$filePath = $this->convertToFilePath( $class );
		$filePath = $this->lowerCamelCase( $filePath );
		$filePath = $this->replaceUnderscores( $filePath );

		return $filePath;
	}

	private function replaceUnderscores( $path ) {

		return str_replace( '_', '-', $path );
	}

	private function lowerCamelCase( $class ) {

		$class = preg_replace( '/([a-z])([A-Z])/', '$1-$2', $class );
		$class = preg_replace( '/([A-Z])([A-Z][a-z])/', '$1-$2', $class );
		$class = strtolower( $class );

		return $class;
	}

	private function convertToFilePath( $class ) {

		$classFile = str_replace( $this->classDirSeparator, DIRECTORY_SEPARATOR, $class );
		$classFile = $classFile . '.php';

		return $classFile;
	}
}

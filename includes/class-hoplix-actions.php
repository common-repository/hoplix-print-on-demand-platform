<?php

class Hoplix_actions {
    
    /**
	 * @return Hoplix_actions
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}
    
    
    
    
}
<?php

namespace amb_dev\CSI;

require_once plugin_dir_path( __FILE__ ) . 'class-churchsuite.php';

use amb_dev\CSI\ChurchSuite as ChurchSuite;


abstract class Cs_View {

    protected ChurchSuite $cs;
    
    public function __construct( ChurchSuite $cs ) {
		$this->cs = $cs;
	}

	public abstract function display() : string;

}

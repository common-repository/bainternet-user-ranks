<?php
/*
Plugin Name: BaInternet User Ranks
Plugin URI: http://www.bainternet.info
Description: Create and display user rank titles based on there post count, comment count or both.
Version: 1.5.2
Author: bainternet
Author URI: http://en.bainternet.info
*/
/*  Copyright 2011 Ohad Raz aKa BaInternet  (email : admin@bainternet.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include('baur.class.php');
add_action('init','new_baur');
function new_baur(){
	$baur_plugin = new baur_Plugin();
}
?>
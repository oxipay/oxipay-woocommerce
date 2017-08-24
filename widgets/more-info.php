<?php
function oxiapy_load_widget() {
	register_widget('oxipay_more_info_widget');
}

class oxipay_more_info_widget extends WP_Widget{

	function __construct() {
		parent::__construct(false, $name = __('Oxipay more info', 'wp_widget_plugin') );
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// This is where you run the code and display the output
		echo ( '<script id="oxipay-banner" src="https://widgets.oxipay.com.au/content/scripts/more-info-large.js"></script>');
		var_dump(parent::get_settings());
		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		?>
		<p>
            Size:
            <br>
            Shape:
            <br>
            Suitable for top/side
		</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}

?>
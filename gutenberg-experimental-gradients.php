<?php
/*
Plugin Name: Gutenberg Experimental Gradients
Plugin URI: https://github.com/ronalfy/gutenberg-experimental-gradients
Description: Enable a variety of gradients for Gutenberg.
Author: Ronald Huereca
Version: 1.0.0
Requires at least: 5.3
Author URI: https://mediaron.com
Contributors: ronalfy
Text Domain: gutenberg-experimental-gradients
Domain Path: /languages

Credit: https://webgradients.com/ for the gradients.
*/

define( 'GUTENBERG_EXPERIMENTAL_GRADIENTS_VERSION', '1.0.0' );
/**
 * Main class where all the stuff happens.
 */
class Gutenberg_Experimental_Gradients {

	/**
	 * Class constructor. Initialize all the actions!
	 */
	public function __construct() {

		// Init admin menu.
		add_action( 'admin_menu', array( $this, 'init_admin_menu' ) );

		// Add scripts/styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'add_scripts' ) );

		// Add Ajax action.
		add_action( 'wp_ajax_geg_save_gradients', array( $this, 'ajax_save_gradients' ) );

		// Maybe add gradients to the theme.
		add_action( 'after_setup_theme', array( $this, 'maybe_add_gradients' ), 100 );
	}

	/**
	 * Add necessary scripts/styles to the plugin.
	 *
	 * @param string $hook Hook name for the admin page.
	 */
	public function add_scripts( $hook ) {
		if ( 'appearance_page_gutenberg-experimental-gradients' !== $hook ) {
			return;
		}
		wp_enqueue_script(
			'gutenberg_experimental_gradients_js',
			plugins_url( 'gradients.js', __FILE__ ),
			array( 'jquery' ),
			GUTENBERG_EXPERIMENTAL_GRADIENTS_VERSION,
			true
		);
		wp_localize_script(
			'gutenberg_experimental_gradients_js',
			'gutenberg_experimental_gradients',
			array(
				'saving' => __( 'Saving...', 'gutenberg-experimental-gradients' ),
				'saved'  => __( 'Saved', 'gutenberg-experimental-gradients' ),
			)
		);
		wp_enqueue_style(
			'gutenberg_experimental_gradients_css',
			plugins_url( 'gradients.css', __FILE__ ),
			array(),
			GUTENBERG_EXPERIMENTAL_GRADIENTS_VERSION,
			'all'
		);
	}

	/**
	 * Save gradients via an Ajax call.
	 */
	public function ajax_save_gradients() {
		if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'save_geg_gradients' ) ) { // phpcs:ignore
			wp_send_json_error();
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$gradients = wp_unslash( filter_input( INPUT_POST, 'gradients', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
		if ( ! is_array( $gradients ) || empty( $gradients ) ) {
			update_option( 'geg_gradients', array() );
			wp_send_json_success();
		}
		foreach ( $gradients as $key => &$data ) {
			$key               = sanitize_text_field( $key );
			$data['name']      = sanitize_text_field( $data['name'] );
			$data['gradient']  = sanitize_text_field( $data['gradient'] );
			$data['slug']      = $key;
			$gradients[ $key ] = $data;
		}
		update_option( 'geg_gradients', $gradients );
		wp_send_json_success();
		die( '' );
	}

	/**
	 * Maybe add gradients to the theme options.
	 */
	public function maybe_add_gradients() {
		// Set gradients. Maybe.
		$gradient_options = get_option( 'geg_gradients', array() );
		if ( is_array( $gradient_options ) && ! empty( $gradient_options ) ) {
			add_theme_support(
				'__experimental-editor-gradient-presets',
				array_values( $gradient_options )
			);
		}
	}

	/**
	 * Initialize the admin menu.
	 */
	public function init_admin_menu() {
		add_submenu_page(
			'themes.php',
			__( 'Gradients', 'gutenberg-experimental-gradients' ),
			__( 'Gradients', 'gutenberg-experimental-gradients' ),
			'manage_options',
			'gutenberg-experimental-gradients',
			array( $this, 'output_admin_menu' )
		);
	}

	/**
	 * Output the admin menu.
	 */
	public function output_admin_menu() {
		?>
		<div class="wrap">
			<h3><?php esc_html_e( 'Select Gradients for Use in Gutenberg', 'gutenberg-experimental-gradients' ); ?></h3>
			<p><?php esc_html_e( 'Requires an up-to-date Gutenberg plugin.', 'gutenberg-experimental-gradients' ); ?></p>
			<p>
				<?php /* translators: %s is the URL to webgradients.com */ ?>
				<?php echo wp_kses_post( sprintf( __( 'Gradients from %s', 'gutenberg-experimental-gradients' ), '<a href="https://webgradients.com/">WebGradients.com</a>' ) ); ?>
			</p>
			<?php
			$gradients = array(
				'linear-gradient(45deg, rgb(255,154,158) 0%, rgb(250,208,196) 99%, rgb(250,208,196) 100%)' => 'Warm Flame',
				'linear-gradient(to top, rgb(161,140,209) 0%, rgb(251,194,235) 100%)' => 'Night Fade',
				'linear-gradient(to top, rgb(250,208,196) 0%, rgb(255,209,255) 100%)' => 'Spring Warmth',
				'linear-gradient(to right, rgb(255,236,210) 0%, rgb(252,182,159) 100%)' => 'Juicy Peach',
				'linear-gradient(to right, rgb(255,129,119) 0%, rgb(255,134,122) 0%, rgb(255,140,127) 21%, rgb(249,145,133) 52%, rgb(207,85,108) 78%, rgb(177,42,91) 100%)' => 'Young Passion',
				'linear-gradient(to top, rgb(255,154,158) 0%, rgb(254,207,239) 99%, rgb(254,207,239) 100%)' => 'Lady Lips',
				'linear-gradient(120deg, rgb(246,211,101) 0%, rgb(253,160,133) 100%)' => 'Sunny Morning',
				'linear-gradient(to top, rgb(251,194,235) 0%, rgb(166,193,238) 100%)' => 'Rainy Ashville',
				'linear-gradient(to top, rgb(253,203,241) 0%, rgb(253,203,241) 1%, rgb(230,222,233) 100%)' => 'Frozen Dreams',
				'linear-gradient(120deg, rgb(161,196,253) 0%, rgb(194,233,251) 100%)' => 'Winter Neva',
				'linear-gradient(120deg, rgb(212,252,121) 0%, rgb(150,230,161) 100%)' => 'Dusty Grass',
				'linear-gradient(120deg, rgb(132,250,176) 0%, rgb(143,211,244) 100%)' => 'Tempting Azure',
				'linear-gradient(to top, rgb(207,217,223) 0%, rgb(226,235,240) 100%)' => 'Heavy Rain',
				'linear-gradient(120deg, rgb(166,192,254) 0%, rgb(246,128,132) 100%)' => 'Amy Crisp',
				'linear-gradient(120deg, rgb(252,203,144) 0%, rgb(213,126,235) 100%)' => 'Mean Fruit',
				'linear-gradient(120deg, rgb(224,195,252) 0%, rgb(142,197,252) 100%)' => 'Deep Blue',
				'linear-gradient(120deg, rgb(240,147,251) 0%, rgb(245,87,108) 100%)' => 'Ripe Malinka',
				'linear-gradient(120deg, rgb(253,251,251) 0%, rgb(235,237,238) 100%)' => 'Cloudy Knoxville',
				'linear-gradient(to right, rgb(79,172,254) 0%, rgb(0,242,254) 100%)' => 'Malibu Beach',
				'linear-gradient(to right, rgb(67,233,123) 0%, rgb(56,249,215) 100%)' => 'New Life',
				'linear-gradient(to right, rgb(250,112,154) 0%, rgb(254,225,64) 100%)' => 'True Sunset',
				'linear-gradient(to top, rgb(48,207,208) 0%, rgb(51,8,103) 100%)' => 'Morpheus Den',
				'linear-gradient(to top, rgb(168,237,234) 0%, rgb(254,214,227) 100%)' => 'Rare Wind',
				'linear-gradient(to top, rgb(94,231,223) 0%, rgb(180,144,202) 100%)' => 'Near Moon',
				'linear-gradient(to top, rgb(210,153,194) 0%, rgb(254,249,215) 100%)' => 'Wild Apple',
				'linear-gradient(135deg, rgb(245,247,250) 0%, rgb(195,207,226) 100%)' => 'Saint Petersburg',
				'radial-gradient(circle 248px at center, rgb(22,217,227) 0%, rgb(48,199,236) 47%, rgb(70,174,247) 100%)' => 'Arielles Smile',
				'linear-gradient(135deg, rgb(102,126,234) 0%, rgb(118,75,162) 100%)' => 'Plum Plate',
				'linear-gradient(135deg, rgb(253,252,251) 0%, rgb(226,209,195) 100%)' => 'Everlasting Sky',
				'linear-gradient(120deg, rgb(137,247,254) 0%, rgb(102,166,255) 100%)' => 'Happy Fisher',
				'linear-gradient(to top, rgb(253,219,146) 0%, rgb(209,253,255) 100%)' => 'Blessing',
				'linear-gradient(to top, rgb(152,144,227) 0%, rgb(177,244,207) 100%)' => 'Sharpeye Eagle',
				'linear-gradient(to top, rgb(235,192,253) 0%, rgb(217,222,216) 100%)' => 'Ladoga Bottom',
				'linear-gradient(to top, rgb(150,251,196) 0%, rgb(249,245,134) 100%)' => 'Lemon Gate',
				'linear-gradient(180deg, rgb(42,245,152) 0%, rgb(0,158,253) 100%)' => 'Itmeo Branding',
				'linear-gradient(to top, rgb(205,156,242) 0%, rgb(246,243,255) 100%)' => 'Zeus Miracle',
				'linear-gradient(to right, rgb(228,175,203) 0%, rgb(184,203,184) 0%, rgb(184,203,184) 0%, rgb(226,197,139) 30%, rgb(194,206,156) 64%, rgb(126,219,220) 100%)' => 'Old Hat',
				'linear-gradient(to right, rgb(184,203,184) 0%, rgb(184,203,184) 0%, rgb(180,101,218) 0%, rgb(207,108,201) 33%, rgb(238,96,156) 66%, rgb(238,96,156) 100%)' => 'Star Wine',
				'linear-gradient(to right, rgb(106,17,203) 0%, rgb(37,117,252) 100%)' => 'Deep Blue',
				'linear-gradient(to top, rgb(55,236,186) 0%, rgb(114,175,211) 100%)' => 'Happy Acid',
				'linear-gradient(to top, rgb(235,187,167) 0%, rgb(207,199,248) 100%)' => 'Awesome Pine',
				'linear-gradient(to top, rgb(255,241,235) 0%, rgb(172,224,249) 100%)' => 'New York',
				'linear-gradient(to right, rgb(238,162,162) 0%, rgb(187,193,191) 19%, rgb(87,198,225) 42%, rgb(180,159,218) 79%, rgb(122,197,216) 100%)' => 'Shy Rainbow',
				'linear-gradient(to top, rgb(196,113,245) 0%, rgb(250,113,205) 100%)' => 'Mixed Hopes',
				'linear-gradient(to top, rgb(72,198,239) 0%, rgb(111,134,214) 100%)' => 'Fly High',
				'linear-gradient(to right, rgb(247,140,160) 0%, rgb(249,116,143) 19%, rgb(253,134,140) 60%, rgb(254,154,139) 100%)' => 'Strong Bliss',
				'linear-gradient(to top, rgb(254,173,166) 0%, rgb(245,239,239) 100%)' => 'Fresh Milk',
				'linear-gradient(to top, rgb(230,233,240) 0%, rgb(238,241,245) 100%)' => 'Snow Again',
				'linear-gradient(to top, rgb(172,203,238) 0%, rgb(231,240,253) 100%)' => 'February Ink',
				'linear-gradient(-20deg, rgb(233,222,250) 0%, rgb(251,252,219) 100%)' => 'Kind Steel',
				'linear-gradient(to top, rgb(193,223,196) 0%, rgb(222,236,221) 100%)' => 'Soft Grass',
				'linear-gradient(to top, rgb(11,163,96) 0%, rgb(60,186,146) 100%)' => 'Grown Early',
				'linear-gradient(to top, rgb(0,198,251) 0%, rgb(0,91,234) 100%)' => 'Sharp Blues',
				'linear-gradient(to right, rgb(116,235,213) 0%, rgb(159,172,230) 100%)' => 'Shady Water',
				'linear-gradient(to top, rgb(106,133,182) 0%, rgb(186,200,224) 100%)' => 'Dirty Beauty',
				'linear-gradient(to top, rgb(163,189,237) 0%, rgb(105,145,199) 100%)' => 'Great Whale',
				'linear-gradient(to top, rgb(151,149,240) 0%, rgb(251,200,212) 100%)' => 'Teen Notebook',
				'linear-gradient(to top, rgb(167,166,203) 0%, rgb(137,137,186) 52%, rgb(137,137,186) 100%)' => 'Polite Rumors',
				'linear-gradient(to top, rgb(63,81,177) 0%, rgb(90,85,174) 13%, rgb(123,95,172) 25%, rgb(143,106,174) 38%, rgb(168,106,164) 50%, rgb(204,107,142) 62%, rgb(241,130,113) 75%, rgb(243,164,105) 87%, rgb(247,201,120) 100%)' => 'Sweet Period',
				'linear-gradient(to top, rgb(252,197,228) 0%, rgb(253,163,75) 15%, rgb(255,120,130) 35%, rgb(200,105,158) 52%, rgb(112,70,170) 71%, rgb(12,29,184) 87%, rgb(2,15,117) 100%)' => 'Wide Matrix',
				'linear-gradient(to top, rgb(219,220,215) 0%, rgb(221,220,215) 24%, rgb(226,201,204) 30%, rgb(231,98,125) 46%, rgb(184,35,90) 59%, rgb(128,19,87) 71%, rgb(61,22,53) 84%, rgb(28,26,39) 100%)' => 'Soft Cherish',
				'linear-gradient(to top, rgb(244,59,71) 0%, rgb(69,58,148) 100%)' => 'Red Salvation',
				'linear-gradient(to top, rgb(79,181,118) 0%, rgb(68,196,137) 30%, rgb(40,169,174) 46%, rgb(40,162,183) 59%, rgb(76,119,136) 71%, rgb(108,79,99) 86%, rgb(67,44,57) 100%)' => 'Burning Spring',
				'linear-gradient(to top, rgb(2,80,197) 0%, rgb(212,63,141) 100%)' => 'Night Party',
				'linear-gradient(to top, rgb(136,211,206) 0%, rgb(110,69,226) 100%)' => 'Sky Glider',
				'linear-gradient(to top, rgb(217,175,217) 0%, rgb(151,217,225) 100%)' => 'Heaven Peach',
				'linear-gradient(to top, rgb(112,40,228) 0%, rgb(229,178,202) 100%)' => 'Purple Division',
				'linear-gradient(15deg, rgb(19,84,122) 0%, rgb(128,208,199) 100%)' => 'Aqua Splash',
				'linear-gradient(to top, rgb(80,82,133) 0%, rgb(88,94,146) 12%, rgb(101,104,159) 25%, rgb(116,116,176) 37%, rgb(126,126,187) 50%, rgb(131,137,199) 62%, rgb(151,149,212) 75%, rgb(162,161,220) 87%, rgb(181,174,228) 100%)' => 'Spiky Naga',
				'linear-gradient(to top, rgb(255,8,68) 0%, rgb(255,177,153) 100%)' => 'Love Kiss',
				'linear-gradient(45deg, rgb(147,165,207) 0%, rgb(228,239,233) 100%)' => 'Cochiti Lake',
				'linear-gradient(to right, rgb(67,67,67) 0%, black 100%)'  => 'Premium Dark',
				'linear-gradient(to top, rgb(12,52,131) 0%, rgb(162,182,223) 100%, rgb(107,140,206) 100%, rgb(162,182,223) 100%)' => 'Cold Evening',
				'linear-gradient(to right, rgb(146,254,157) 0%, rgb(0,201,255) 100%)' => 'Summer Games',
				'linear-gradient(to right, rgb(255,117,140) 0%, rgb(255,126,179) 100%)' => 'Passionate Bed',
				'linear-gradient(to right, rgb(134,143,150) 0%, rgb(89,97,100) 100%)' => 'Mountain Rock',
				'linear-gradient(to top, rgb(199,144,129) 0%, rgb(223,165,121) 100%)' => 'Desert Hump',
				'linear-gradient(45deg, rgb(139,170,170) 0%, rgb(174,139,156) 100%)' => 'Jungle Day',
				'linear-gradient(to right, rgb(248,54,0) 0%, rgb(249,212,35) 100%)' => 'Phoenix Start',
				'linear-gradient(-20deg, rgb(183,33,255) 0%, rgb(33,212,253) 100%)' => 'October Silence',
				'linear-gradient(-20deg, rgb(110,69,226) 0%, rgb(136,211,206) 100%)' => 'Faraway River',
				'linear-gradient(-20deg, rgb(213,88,200) 0%, rgb(36,210,146) 100%)' => 'Alchemist Lab',
				'linear-gradient(60deg, rgb(171,236,214) 0%, rgb(251,237,150) 100%)' => 'Over Sun',
				'linear-gradient(to top, rgb(213,212,208) 0%, rgb(213,212,208) 1%, rgb(238,238,236) 31%, rgb(239,238,236) 75%, rgb(233,233,231) 100%)' => 'Premium White',
				'linear-gradient(to top, rgb(95,114,189) 0%, rgb(155,35,234) 100%)' => 'Mars Party',
				'linear-gradient(to top, rgb(9,32,63) 0%, rgb(83,120,149) 100%)' => 'Eternal Constance',
				'linear-gradient(-20deg, rgb(221,214,243) 0%, rgb(250,172,168) 100%, rgb(250,172,168) 100%)' => 'Japan Blush',
				'linear-gradient(-20deg, rgb(220,176,237) 0%, rgb(153,201,156) 100%)' => 'Smiling Rain',
				'linear-gradient(to top, rgb(243,231,233) 0%, rgb(227,238,255) 99%, rgb(227,238,255) 100%)' => 'Cloudy Apple',
				'linear-gradient(to top, rgb(199,29,111) 0%, rgb(208,150,147) 100%)' => 'Big Mango',
				'linear-gradient(60deg, rgb(150,222,218) 0%, rgb(80,201,195) 100%)' => 'Healthy Water',
				'linear-gradient(to top, rgb(247,112,98) 0%, rgb(254,81,150) 100%)' => 'Amour Amour',
				'linear-gradient(to top, rgb(196,197,199) 0%, rgb(220,221,223) 52%, rgb(235,235,235) 100%)' => 'Risky Concrete',
				'linear-gradient(to right, rgb(168,202,186) 0%, rgb(93,65,87) 100%)' => 'Strong Stick',
				'linear-gradient(60deg, rgb(41,50,60) 0%, rgb(72,85,99) 100%)' => 'Vicious Stance',
				'linear-gradient(-60deg, rgb(22,160,133) 0%, rgb(244,208,63) 100%)' => 'Palo Alto',
				'linear-gradient(-60deg, rgb(255,88,88) 0%, rgb(240,152,25) 100%)' => 'Happy Memories',
				'linear-gradient(-20deg, rgb(43,88,118) 0%, rgb(78,67,118) 100%)' => 'Midnight Bloom',
				'linear-gradient(-20deg, rgb(0,205,172) 0%, rgb(141,218,213) 100%)' => 'Crystalline',
				'linear-gradient(to top, rgb(68,129,235) 0%, rgb(4,190,254) 100%)' => 'River City',
				'linear-gradient(to top, rgb(218,212,236) 0%, rgb(218,212,236) 1%, rgb(243,231,233) 100%)' => 'Confident Cloud',
				'linear-gradient(45deg, rgb(135,77,162) 0%, rgb(196,58,48) 100%)' => 'Le Cocktail',
				'linear-gradient(to top, rgb(232,25,139) 0%, rgb(199,234,253) 100%)' => 'Frozen Berry',
				'linear-gradient(-20deg, rgb(247,148,164) 0%, rgb(253,214,189) 100%)' => 'Child Care',
				'linear-gradient(60deg, rgb(100,179,244) 0%, rgb(194,229,156) 100%)' => 'Flying Lemon',
				'linear-gradient(to top, rgb(59,65,197) 0%, rgb(169,129,187) 49%, rgb(255,200,169) 100%)' => 'New Retrowave',
				'linear-gradient(to top, rgb(15,216,80) 0%, rgb(249,240,71) 100%)' => 'Hidden Jaguar',
				'linear-gradient(to top, lightgrey 0%, lightgrey 1%, rgb(224,224,224) 26%, rgb(239,239,239) 48%, rgb(217,217,217) 75%, rgb(188,188,188) 100%)' => 'Above The Sky',
				'linear-gradient(45deg, rgb(238,156,167) 0%, rgb(255,221,225) 100%)' => 'Nega',
				'linear-gradient(to right, rgb(58,181,176) 0%, rgb(61,153,190) 31%, rgb(86,49,122) 100%)' => 'Dense Water',
				'linear-gradient(to top, rgb(32,156,255) 0%, rgb(104,224,207) 100%)' => 'Seashore',
				'linear-gradient(to top, rgb(189,194,232) 0%, rgb(189,194,232) 1%, rgb(230,222,233) 100%)' => 'Marble Wall',
				'linear-gradient(to top, rgb(230,185,128) 0%, rgb(234,205,163) 100%)' => 'Cheerful Caramel',
				'linear-gradient(to top, rgb(30,60,114) 0%, rgb(30,60,114) 1%, rgb(42,82,152) 100%)' => 'Night Sky',
				'linear-gradient(to top, rgb(213,222,231) 0%, rgb(255,175,189) 0%, rgb(201,255,191) 100%)' => 'Magic Lake',
				'linear-gradient(to top, rgb(155,225,93) 0%, rgb(0,227,174) 100%)' => 'Young Grass',
				'linear-gradient(to right, rgb(237,110,160) 0%, rgb(236,140,105) 100%)' => 'Royal Garden',
				'linear-gradient(to right, rgb(255,195,160) 0%, rgb(255,175,189) 100%)' => 'Gentle Care',
				'linear-gradient(to top, rgb(204,32,142) 0%, rgb(103,19,210) 100%)' => 'Plum Bath',
				'linear-gradient(to top, rgb(179,255,171) 0%, rgb(18,255,247) 100%)' => 'Happy Unicorn',
				'linear-gradient(-45deg, rgb(255,199,150) 0%, rgb(255,107,149) 100%)' => 'African Field',
				'linear-gradient(to right, rgb(36,57,73) 0%, rgb(81,127,164) 100%)' => 'Solid Stone',
				'linear-gradient(-20deg, rgb(252,96,118) 0%, rgb(255,154,68) 100%)' => 'Orange Juice',
				'linear-gradient(to top, rgb(223,233,243) 0%, white 100%)' => 'Glass Water',
				'linear-gradient(to right, rgb(0,219,222) 0%, rgb(252,0,255) 100%)' => 'North Miracle',
				'linear-gradient(to right, rgb(249,212,35) 0%, rgb(255,78,80) 100%)' => 'Fruit Blend',
				'linear-gradient(to top, rgb(80,204,127) 0%, rgb(245,209,0) 100%)' => 'Millennium Pine',
				'linear-gradient(to right, rgb(10,207,254) 0%, rgb(73,90,255) 100%)' => 'High Flight',
				'linear-gradient(-20deg, rgb(97,97,97) 0%, rgb(155,197,195) 100%)' => 'Mole Hall',
				'linear-gradient(60deg, rgb(61,51,147) 0%, rgb(43,118,185) 37%, rgb(44,172,209) 65%, rgb(53,235,147) 100%)' => 'Space Shift',
				'linear-gradient(to top, rgb(223,137,181) 0%, rgb(191,217,254) 100%)' => 'Forest Inei',
				'linear-gradient(to right, rgb(215,210,204) 0%, rgb(48,67,82) 100%)' => 'Rich Metal',
				'linear-gradient(to top, rgb(225,79,173) 0%, rgb(249,212,35) 100%)' => 'Juicy Cake',
				'linear-gradient(to top, rgb(178,36,239) 0%, rgb(117,121,255) 100%)' => 'Smart Indigo',
				'linear-gradient(to right, rgb(193,193,97) 0%, rgb(193,193,97) 0%, rgb(212,212,177) 100%)' => 'Sand Strike',
				'linear-gradient(to right, rgb(236,119,171) 0%, rgb(120,115,245) 100%)' => 'Norse Beauty',
				'linear-gradient(to top, rgb(0,122,223) 0%, rgb(0,236,188) 100%)' => 'Aqua Guidance',
				'linear-gradient(-225deg, rgb(32,226,215) 0%, rgb(249,254,165) 100%)' => 'Sun Veggie',
				'linear-gradient(-225deg, rgb(44,216,213) 0%, rgb(197,193,255) 56%, rgb(255,186,195) 100%)' => 'Sea Lord',
				'linear-gradient(-225deg, rgb(44,216,213) 0%, rgb(107,141,214) 48%, rgb(142,55,215) 100%)' => 'Black Sea',
				'linear-gradient(-225deg, rgb(223,255,205) 0%, rgb(144,249,196) 48%, rgb(57,243,187) 100%)' => 'Grass Shampoo',
				'linear-gradient(-225deg, rgb(93,159,255) 0%, rgb(184,220,255) 48%, rgb(107,187,255) 100%)' => 'Landing Aircraft',
				'linear-gradient(-225deg, rgb(168,191,255) 0%, rgb(136,77,128) 100%)' => 'Witch Dance',
				'linear-gradient(-225deg, rgb(82,113,196) 0%, rgb(177,159,255) 48%, rgb(236,161,254) 100%)' => 'Sleepless Night',
				'linear-gradient(-225deg, rgb(255,226,159) 0%, rgb(255,169,159) 48%, rgb(255,113,154) 100%)' => 'Angel Care',
				'linear-gradient(-225deg, rgb(34,225,255) 0%, rgb(29,143,225) 48%, rgb(98,94,177) 100%)' => 'Crystal River',
				'linear-gradient(-225deg, rgb(182,206,232) 0%, rgb(245,120,220) 100%)' => 'Soft Lipstick',
				'linear-gradient(-225deg, rgb(255,254,255) 0%, rgb(215,255,254) 100%)' => 'Salt Mountain',
				'linear-gradient(-225deg, rgb(227,253,245) 0%, rgb(255,230,250) 100%)' => 'Perfect White',
				'linear-gradient(-225deg, rgb(125,226,252) 0%, rgb(185,182,229) 100%)' => 'Fresh Oasis',
				'linear-gradient(-225deg, rgb(203,186,204) 0%, rgb(37,128,179) 100%)' => 'Strict November',
				'linear-gradient(-225deg, rgb(183,248,219) 0%, rgb(80,167,194) 100%)' => 'Morning Salad',
				'linear-gradient(-225deg, rgb(112,133,182) 0%, rgb(135,167,217) 50%, rgb(222,243,248) 100%)' => 'Deep Relief',
				'linear-gradient(-225deg, rgb(119,255,210) 0%, rgb(98,151,219) 48%, rgb(30,236,255) 100%)' => 'Sea Strike',
				'linear-gradient(-225deg, rgb(172,50,228) 0%, rgb(121,24,242) 48%, rgb(72,1,255) 100%)' => 'Night Call',
				'linear-gradient(-225deg, rgb(212,255,236) 0%, rgb(87,242,204) 48%, rgb(69,150,251) 100%)' => 'Supreme Sky',
				'linear-gradient(-225deg, rgb(158,251,211) 0%, rgb(87,233,242) 48%, rgb(69,212,251) 100%)' => 'Light Blue',
				'linear-gradient(-225deg, rgb(71,59,123) 0%, rgb(53,132,167) 51%, rgb(48,210,190) 100%)' => 'Mind Crawl',
				'linear-gradient(-225deg, rgb(101,55,155) 0%, rgb(136,106,234) 53%, rgb(100,87,198) 100%)' => 'Lily Meadow',
				'linear-gradient(-225deg, rgb(164,69,178) 0%, rgb(212,24,114) 52%, rgb(255,0,102) 100%)' => 'Sugar Lollipop',
				'linear-gradient(-225deg, rgb(119,66,178) 0%, rgb(241,128,255) 52%, rgb(253,139,217) 100%)' => 'Sweet Dessert',
				'linear-gradient(-225deg, rgb(255,60,172) 0%, rgb(86,43,124) 52%, rgb(43,134,197) 100%)' => 'Magic Ray',
				'linear-gradient(-225deg, rgb(255,5,124) 0%, rgb(141,11,147) 50%, rgb(50,21,117) 100%)' => 'Teen Party',
				'linear-gradient(-225deg, rgb(255,5,124) 0%, rgb(124,100,213) 48%, rgb(76,195,255) 100%)' => 'Frozen Heat',
				'linear-gradient(-225deg, rgb(105,234,203) 0%, rgb(234,204,248) 48%, rgb(102,84,241) 100%)' => 'Gagarin View',
				'linear-gradient(-225deg, rgb(35,21,87) 0%, rgb(68,16,122) 29%, rgb(255,19,97) 67%, rgb(255,248,0) 100%)' => 'Fabled Sunset',
				'linear-gradient(-225deg, rgb(61,78,129) 0%, rgb(87,83,201) 48%, rgb(110,127,243) 100%)' => 'Perfect Blue',
			);
			?>
			<div id="geg-gradients">
				<form method="POST" id="geg-gradients-form">
					<?php
					wp_nonce_field( 'save_geg_gradients', 'geg_ajax_gradients_nonce' );
					?>
					<?php
					$gradient_options = get_option( 'geg_gradients', array() );
					foreach ( $gradients as $style => $name ) {
						$slug = sanitize_title( $name );
						?>
						<button class="geg-gradient <?php echo esc_attr( isset( $gradient_options[ $slug ] ) ? 'checked' : 'unchecked' ); ?>" arial-label="<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( $name ); ?>" data-title="<?php echo esc_attr( $slug ); ?>" data-name="<?php echo esc_attr( $name ); ?>" data-style="<?php echo esc_attr( $style ); ?>"><span style="background-image: <?php echo esc_attr( $style ); ?>;"></span></button>
						<?php
					}
					?>
					<div>
					<a href="#" id="geg-gradient-select-all"><?php esc_html_e( 'Select All', 'gutenberg-experimental-gradients' ); ?></a> | <a href="#" id="geg-gradient-deselect-all"><?php esc_html_e( 'Deselect All', 'gutenberg-experimental-gradients' ); ?></a>
					</div>
					<?php submit_button( __( 'Save Gradients', 'gutenberg-experimental-gradients' ), 'primary', 'geg-save-gradients' ); ?>
				</form>
			</div>
		</div>
		<?php
	}
}
new Gutenberg_Experimental_Gradients();

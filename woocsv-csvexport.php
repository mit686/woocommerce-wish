<?php
/*
Plugin Name: WooCommerce Wish Lister
#Plugin URI: http://allaerd.org/
Description: WooCommerce Wish Integration
Version: 1.0.0
Author: Allaerd Mensonides, Dmitry Gorobets
License: GPLv2 or later
Author URI: http://allaerd.org
*/

//include the classes
include dirname( __FILE__ ) . '/include/class-woocsv-export-product.php';
include dirname( __FILE__ ) . '/include/class-woocsv-export-variation.php';

//start
$woocsvExport = new woocsvExport();

class woocsvExport
{

	public function __construct()
	{
		//set up menu
		add_action('admin_menu', array($this,'menu') );

		//activate
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
	}

	public function menu()
	{
		//add the page
		$page = add_menu_page('WooCommerce Wish Lister', 'Wish', 'administrator', 'woocsv_export', array($this,'content'), '', '57.2307');
	}

	public function activate() {
		$options = get_option('woocsv-lastrun-export');
		if (empty($options)) {
			update_option('woocsv-lastrun-export', array('date'=>'','filename'=>''));
		}
	}

	public function content() {
	if (isset($_POST['action']) && $_POST['action'] == 'export') {
		$this->export();
	}
	$options = get_option('woocsv-lastrun-export');

	?>
	<div class="wrap">
		<h2>WooCommerce Wish Lister</h2>
		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<h4>Export to Wish</h4>
						<p class="message">
						<form method="post">
							<input type="hidden" name="action" value="export">
							<button type="submit" class="button-primary">Export</button>
						</form>
						<?php if ($options) : ?>
						Last export: <?php echo $options['date'];?><br/>
						File: <a href="<?php echo $options['filename'];?>">export.csv</a>
						<?php endif;?>
						</p>
					</div>
					<!--
					<div class="welcome-panel-column">
						<h4>Allaerd.org</h4>
						<ul>
							<li><a href="http://allaerd.org/documentation" target="_blank">Documentation</a></li>
							<li><a href="http://contactform7.com/admin-screen/" target="_blank">Admin Screen</a></li>
							<li><a href="http://contactform7.com/tag-syntax/" target="_blank">How Tags Work</a></li>
							<li><a href="http://contactform7.com/setting-up-mail/" target="_blank">Setting Up Mail</a></li>
						</ul>
					</div>
					-->
					<div class="welcome-panel-column">
						<h4>WooCommerce Wish Lister</h4>
						Ver. 1.0.0
					</div>
				</div>
			</div>
		</div>
		<!--<div id="welcome-panel" class="welcome-panel gray"></div>-->
	</div>
<?php
	}

	public function export()
	{

		//set upload dir
		$upload_dir = wp_upload_dir();

		//create an export file
		$filename = $upload_dir['basedir'].'/export.csv';
		$fileurl = $upload_dir['baseurl'].'/export.csv';

		//delete it if it is there
		if (file_exists($filename)) {
			unlink($filename);
		}

		$fp = fopen( $filename, 'w');
//		$fp = fopen('php://output', 'w');

		//add header
		$product = new woocsvExportProduct();
		fputcsv($fp, $product->getHeader());

		//get product_ids
		/* !1.0.1 added post_per_page */
		$product_ids = get_posts(array('posts_per_page'=>-1,'post_type'=>'product','fields'=>'ids'));
		//echo $product_ids;
		$product_ids=array('7384');

		//loop through posts
		foreach ($product_ids as $product_id) {

			$products = new woocsvExportProduct($product_id);
			$lines = $products->getProduct();

			foreach ($lines as $line) {
				//and write it to the handle
				fputcsv($fp, $line);
			}
		}

		//close file
		fclose($fp);
		echo '</pre>';

		update_option('woocsv-lastrun-export',array('date'=>date("Y-m-d H:i:s"),'filename'=>$fileurl));

	}
}

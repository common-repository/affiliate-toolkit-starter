<?php


class atkp_queue_view {

	private $action;
	private $queueid;
	/**
	 * @var bool|mixed
	 */
	private bool $saving;

	public function __construct() {

		$this->action  = ATKPTools::get_get_parameter( 'action', 'string' );
		$this->queueid = ATKPTools::get_get_parameter( 'queueid', 'int' );
		if ( $this->action == '' ) {
			$this->action = ATKPTools::get_post_parameter( 'action', 'string' );
		}
		$this->saving = ATKPTools::get_post_parameter( 'saving', 'bool' );
		if ( $this->queueid == '' ) {
			$this->queueid = ATKPTools::get_post_parameter( 'queueid', 'int' );
		}

		add_action( 'atkp_register_submenu', array( &$this, 'register_admin_menu' ), 10, 1 );
		if ( $this->action == '' ) {
			add_filter( 'set-screen-option', [ &$this, 'set_screen' ], 10, 3 );
		}
	}

	public function set_screen( $status, $option, $value ) {
		return $value;
	}

	private $atkp_queue_table;
	private $atkp_queue_entry_table;

	public function register_admin_menu( $parentmenu ) {


		global $submenu;

		$hook = add_submenu_page(

			$parentmenu,
			__( 'Queues', ATKP_PLUGIN_PREFIX ),
			__( 'Queues', ATKP_PLUGIN_PREFIX ),
			'edit_pages',
			ATKP_PLUGIN_PREFIX . '_viewqueue',
			array( &$this, 'show_page' )
		);


		if ( $this->action == '' ) {
			add_action( "load-$hook", [ $this, 'screen_option' ] );
		} else if ( $this->action == 'detail' ) {
			add_action( "load-$hook", [ $this, 'screen_option_entries' ] );
		}
	}

	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => __( 'Queues', ATKP_PLUGIN_PREFIX ),
			'default' => 25,
			'option'  => 'queues_per_page'
		];

		add_screen_option( $option, $args );

		$this->atkp_queue_table = new atkp_queue_table();
	}

	public function screen_option_entries() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Queue Entries',
			'default' => 25,
			'option'  => 'queues_per_page'
		];

		add_screen_option( $option, $args );

		$this->atkp_queue_entry_table = new atkp_queue_entry_table();
	}

	public function show_page() {
		$atkp_queuetable_helper = new atkp_queuetable_helper();
		if ( ! $atkp_queuetable_helper->exists_table()[0] ) {
			echo esc_html__( 'database table does not exists: ' . $atkp_queuetable_helper->get_queuetable_tablename(), ATKP_PLUGIN_PREFIX );

			return;
		}

		if ( $this->action == '' ) {
			$delete_nonce = wp_create_nonce( 'atkp_edit_queue' );

			?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php echo esc_html__( 'Queues', ATKP_PLUGIN_PREFIX ) ?></h1>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <form method="post">
									<?php
									if ( $this->atkp_queue_table != null ) {
										$this->atkp_queue_table->prepare_items();
										$this->atkp_queue_table->display();
									}
									?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
			<?php
		} else if ( $this->action == 'detail' ) {

			$atkp_queue = $this->queueid != '' ? atkp_queue::load( $this->queueid ) : null;

			?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php echo esc_html__( 'Queue Entries', ATKP_PLUGIN_PREFIX ) ?></h1>

                <div id="poststuff">
                    <div id="post-body" class="metabox-holder">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <form method="post">
									<?php
									if ( $this->atkp_queue_entry_table != null ) {
										atkp_queue_entry_table::$queue = $atkp_queue;
										$this->atkp_queue_entry_table->prepare_items();
										$this->atkp_queue_entry_table->display();
									}
									?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
			<?php


		} else if ( $this->action == 'delete' ) {

			$obj = atkp_queue::load( intval( $this->queueid ) );

			$obj->delete();

			wp_redirect( sprintf( '?page=%s', esc_attr( $_REQUEST['page'] ) ) );
			exit;
		}


	}

}
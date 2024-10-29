<?php


class atkp_template_view {

	private $action;
	private $templateid;
	private $templatename;
	/**
	 * @var bool|mixed
	 */
	private bool $saving;

	public function __construct() {

		$this->action       = ATKPTools::get_get_parameter( 'action', 'string' );
		$this->templateid   = ATKPTools::get_get_parameter( 'templateid', 'string' );
		$this->templatename = ATKPTools::get_get_parameter( 'templatename', 'string' );
		if ( $this->action == '' ) {
			$this->action = ATKPTools::get_post_parameter( 'action', 'string' );
		}
		$this->saving = ATKPTools::get_post_parameter( 'saving', 'bool' );
		if ( $this->templateid == '' ) {
			$this->templateid = ATKPTools::get_post_parameter( 'templateid', 'string' );
		}

		add_action( 'atkp_register_submenu', array( &$this, 'register_admin_menu' ), 10, 1 );
		if ( $this->action == '' ) {
			add_filter( 'set-screen-option', [ &$this, 'set_screen' ], 10, 3 );
		}
	}

	public function set_screen( $status, $option, $value ) {
		return $value;
	}

	private $atkp_template_table;

	public function register_admin_menu( $parentmenu ) {


		global $submenu;

		$hook = add_submenu_page(

			$parentmenu,
			esc_html__('Templates', ATKP_PLUGIN_PREFIX ),
			esc_html__( 'Templates', ATKP_PLUGIN_PREFIX ),
			'edit_pages',
			ATKP_PLUGIN_PREFIX . '_viewtemplate',
			array( &$this, 'show_page' )
		);


		if ( $this->action == '' ) {
			add_action( "load-$hook", [ $this, 'screen_option' ] );
		}
	}

	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Templates',
			'default' => 50,
			'option'  => 'templates_per_page'
		];

		add_screen_option( $option, $args );

		$this->atkp_template_table = new atkp_template_table();
	}

	private function import_template( $contents, $template_name = '', $regenerate_styles = true ) {
		try {

			$mytemplate = unserialize( html_entity_decode( $contents ) );

			if ( $mytemplate == null ) {
				$mytemplate = json_decode( $contents );
			}

			if ( isset( $mytemplate->data ) ) {
				$mytemplate = $mytemplate->data;
			}

			$post_id = null;

			if ( $mytemplate == null ) {
				return 'template not readable: ' . $template_name;
			} else if ( isset( $mytemplate->fields ) ) {

				$fields = array_keys( get_object_vars( $mytemplate->fields ) );

				$my_post = array(
					'post_title'  => $mytemplate->template_name,
					'post_type'   => ATKP_TEMPLATE_POSTTYPE,
					'post_status' => 'publish',
				);

				// Insert the post into the database
				$post_id = wp_insert_post( $my_post );

				foreach ( $fields as $field ) {

					$unval = is_array( $mytemplate->fields->$field ) ? ( count( $mytemplate->fields->$field ) > 0 ? $mytemplate->fields->$field[0] : null ) : $mytemplate->fields->$field;

					if ( $unval != null ) {
						$data = @unserialize( $unval );

						if ( $data !== false ) {
							$unval = $data;
						}
					}
					update_post_meta( $post_id, $field, $unval );
				}

			} else {

				if ( $mytemplate['template_type'] == '' ) {
					$mytemplate['template_type'] = 6;
				}

				$my_post = array(
					'post_title' => $template_name == '' ? $mytemplate['title'] : $template_name,
					'post_type'   => ATKP_TEMPLATE_POSTTYPE,
					'post_status' => 'publish',
				);

				// Insert the post into the database
				$post_id = wp_insert_post( $my_post );

				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_disabledisclaimer', $mytemplate['disableddisclaimer'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_extendedview', $mytemplate['extendedview'] );

				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_template_type', $mytemplate['template_type'] );

				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_header', $mytemplate['header'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body_header', $mytemplate['bodyheader'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_detail_header', $mytemplate['detailheader'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_detail_footer', $mytemplate['detailfooter'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body', $mytemplate['body'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_body_footer', $mytemplate['bodyfooter'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_footer', $mytemplate['footer'] );

				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_css', $mytemplate['css'] );


				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_comparevalues', $mytemplate['comparevalues'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_horizontalscrollbars', $mytemplate['horizontalscrollbars'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_hideheaders', $mytemplate['hideheaders'] );

				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_maxmobileproducts', $mytemplate['maxmobileproducts'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_maxproducts', $mytemplate['maxproducts'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_viewtype', $mytemplate['viewtype'] );
				ATKPTools::set_post_setting( $post_id, ATKP_TEMPLATE_POSTTYPE . '_mobilebody', $mytemplate['mobilebody'] );

				$imported = true;
			}

			if ( $regenerate_styles ) {
				ATKPTools::write_global_scripts();
				ATKPTools::write_global_styles();
			}

			wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $post_id ) );

			return $post_id;

		} catch ( Exception $e ) {
			return 'Unknown content!<br />' . 'Fehler: ' . $e->getMessage();
		}

		return null;
	}

	public function show_page() {
		$importmessage = '';

		if ( $this->action == '' || $this->action == 'import' ) {
			$delete_nonce = wp_create_nonce( 'atkp_edit_queue' );

			$imported = false;
			if ( ATKPTools::exists_post_parameter( 'saveimporttemplate' ) && check_admin_referer( 'save', 'save' ) ) {

				//reads the name of the file the user submitted for uploading
				$templatefile = $_FILES[ ATKP_PLUGIN_PREFIX . '_filetemplate' ]['name'];
				//if it is not empty
				if ( $templatefile ) {

					//get the original name of the file from the clients machine
					$filename = stripslashes( $_FILES[ ATKP_PLUGIN_PREFIX . '_filetemplate' ]['name'] );
					//get the extension of the file in a lower case format
					$extension = pathinfo( $filename, PATHINFO_EXTENSION );
					$extension = strtolower( $extension );
					//if it is not a known extension, we will suppose it is an error and will not upload the file,
					//we will only allow .ttf and .otf file extensions
					//otherwise we will do more tests
					if ( $extension != "txt" && $extension != "json" ) {
						//print error message
						$importmessage = 'Unknown fileextension!';
						$errors        = 1;
					} else {

						$contents = file_get_contents( $_FILES[ ATKP_PLUGIN_PREFIX . '_filetemplate' ]['tmp_name'] );

						$importmessage = $this->import_template( $contents );

					}


				} else {
					$importmessage = '<b>file not found</b>';
				}
			}

			?>
            <div class="wrap">
                <h1 class="wp-heading-inline"><?php echo esc_html__( 'Templates', ATKP_PLUGIN_PREFIX ) ?></h1>
                <a href="<?php echo esc_url(admin_url( 'post-new.php?post_type=atkp_template' ) ); ?>"
                   class="page-title-action"><?php echo esc_html__( 'Add New', ATKP_PLUGIN_PREFIX ) ?></a>
                <a href="<?php echo esc_url(admin_url( 'admin.php?page=ATKP_viewtemplate&action=import' ) ); ?>"
                   class="page-title-action"><?php echo esc_html__( 'Import template', ATKP_PLUGIN_PREFIX ) ?></a>
                <hr class="wp-header-end">
                <h2 class="screen-reader-text">Filter pages list</h2>

				<?php if ( $this->action == 'import' ) { ?>

                    <form method="POST"
                          action="<?php echo esc_url(admin_url( 'admin.php?page=ATKP_viewtemplate&action=import' ) ); ?>"
                          enctype="multipart/form-data">
						<?php wp_nonce_field( "save", "save" ); ?>


                        <div class="atkp-content wrap" style="margin-bottom:30px;float:none !important">

                            <div class="inner metabox-holder ">

                                <div id="postbox-container-2" class="postbox-container" style="float:none">
                                    <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                        <div id="atkp_product_shop_box" class="postbox ">
                                            <div class="postbox-header"><h2
                                                        class="hndle ui-sortable-handle"><?php echo esc_html__( 'Upload your template', ATKP_PLUGIN_PREFIX ) ?></h2>
                                            </div>
                                            <div class="inside">

                                                <table class="form-table">
                                                    <tr>
                                                        <th scope="row">
                                                            <label for="">
	                                                            <?php echo esc_html__( 'Template file', ATKP_PLUGIN_PREFIX ) ?>
                                                            </label>
                                                        </th>
                                                        <td>
                                                            <input type="file"
                                                                   name="<?php echo esc_attr(ATKP_PLUGIN_PREFIX . '_filetemplate') ?>">
															<?php ATKPTools::display_helptext( 'Upload your exported template. The file extension must be ".txt" to import the file.' ) ?>

                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td colspan="2"><span
                                                                    style="font-weight:bold;color:red"><?php echo esc_html__( $importmessage, ATKP_PLUGIN_PREFIX ); ?></span>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td></td>
                                                        <td>
															<?php submit_button( esc_html__('Import template', ATKP_PLUGIN_PREFIX ), 'primary', 'saveimporttemplate', false ); ?>
                                                        </td>
                                                    </tr>

                                                </table>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>


                    </form>

				<?php } else { ?>

					<?php $this->atkp_template_table->views(); ?>

                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder">
                            <div id="post-body-content">
                                <div class="meta-box-sortables ui-sortable">
                                    <form method="post">
										<?php
										if ( $this->atkp_template_table != null ) {
											$this->atkp_template_table->prepare_items();
											$this->atkp_template_table->display();

										}
										?>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <br class="clear">
                    </div>

                    <style>
                        .atkp-template-dropdown {
                            position: relative;
                            display: inline-block;
                        }

                        .atkp-template-dropdown-content {
                            display: none;
                            position: absolute;
                            background-color: #f9f9f9;
                            min-width: 160px;
                            box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
                            z-index: 1;
                        }

                        .atkp-template-dropdown:hover .atkp-template-dropdown-content {
                            display: block;
                        }

                        .atkp-template-desc {
                            padding: 15px;
                            text-align: center;
                        }

                        /*
                        .tablenav {
                            display: none;
                        }*/
                        .subsubsub {
                            margin-bottom: 10px;
                        }
                    </style>

				<?php } ?>

            </div>
			<?php
		} else if ( $this->action == 'clone' ) {
			$importpath = apply_filters( 'atkp_template_import_filename', '', $this->templateid );

			//copy template
			if ( $importpath != '' ) {
				$contents = file_get_contents( $importpath );

				$new_post_id = $this->import_template( $contents, $this->templatename . ' (2)', false );

				if ( is_numeric( $new_post_id ) ) {
					echo '<script>window.location.replace("' . esc_url( admin_url( 'post.php?action=edit&post=' . $new_post_id ) ) . '");</script>';
				} else {
					echo '<p>' . esc_html__( $new_post_id, ATKP_PLUGIN_PREFIX ) . '</p>';
				}
			} else {
				$args = array(
					'post_status' => 'draft',
					'post_title'  => $this->templatename . ' (2)',
					'post_type'   => ATKP_TEMPLATE_POSTTYPE,
				);
				/*
				 * insert the post by wp_insert_post() function
				 */
				$new_post_id = wp_insert_post( $args );

				if ( is_numeric( $this->templateid ) ) {
					global $wpdb;
					//custom template
					$post_meta_infos = $wpdb->get_results( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$this->templateid" );
					if ( count( $post_meta_infos ) != 0 ) {
						$sql_query     = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
						$sql_query_sel = array();
						foreach ( $post_meta_infos as $meta_info ) {
							$meta_key = $meta_info->meta_key;
							if ( $meta_key == '_wp_old_slug' ) {
								continue;
							}
							$meta_value      = addslashes( $meta_info->meta_value );
							$sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
						}
						$sql_query .= implode( " UNION ALL ", $sql_query_sel );
						$wpdb->query( $sql_query );
					}


				} else {
					//systemtemplate
					$mytemplate     = apply_filters( 'atkp_template_get_blade', '', $this->templateid );
					$mycss          = apply_filters( 'atkp_template_get_css', '', $this->templateid );
					$mytemplatetype = apply_filters( 'atkp_template_get_type', '6', $this->templateid );

					ATKPTools::set_post_setting( $new_post_id, 'atkp_template_template_type', $mytemplatetype );
					ATKPTools::set_post_setting( $new_post_id, 'atkp_template_css', $mycss );
					ATKPTools::set_post_setting( $new_post_id, 'atkp_template_body', $mytemplate );
				}

				echo '<script>window.location.replace("' . esc_url(admin_url( 'post.php?action=edit&post=' . $new_post_id )) . '");</script>';
				//wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
			}


		} else if ( $this->action == 'delete' ) {

			wp_delete_post( $this->templateid, true );
			echo '<script>window.location.replace("' . sprintf( '?page=%s', esc_attr( $_REQUEST['page'] ) ) . '");</script>';
			exit;
		}


	}

}
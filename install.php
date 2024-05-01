<?php

/**
 * @author Webkul
 */
if ( ! class_exists( 'WK_WC_POS_Install_Schema' ) ) {

	class WK_WC_POS_Install_Schema {

		public function __construct() {

			$this->wkwcpos_flush_permalinks();

		}
		public function wk_wc_pos_create_tables() {

			$role = get_role( 'pos_user' );

			if ( empty( $role ) ) {
				add_role(
					'pos_user',
					'Pos User',
					array(
						'read'    => true,
						'level_0' => true,
					)
				);
			}

			global $wpdb;

			$wpdb->hide_errors();

			$charset_collate = $wpdb->get_charset_collate();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql = "
                CREATE TABLE {$wpdb->prefix}woocommerce_pos_outlets (
                    `id` bigint(20) NOT NULL auto_increment,
                    `outlet_name` varchar(100) NOT NULL,
                    `outlet_address` varchar(100) NOT NULL,
                    `outlet_status` varchar(20) NOT NULL,
                    `outlet_city` varchar(200) NOT NULL,
                    `outlet_state` varchar(200) NOT NULL,
                    `outlet_postcode` varchar(200) NOT NULL,
                    `outlet_country` varchar(200) NOT NULL,
                    `outlet_payment` longtext NOT NULL,
                    `outlet_invoice` bigint(20) NOT NULL,
                    PRIMARY KEY (`id`)
                ) $charset_collate;
                CREATE TABLE {$wpdb->prefix}woocommerce_pos_outlet_map (
                    `id` int(20) NOT NULL auto_increment,
                    `outlet_id` int(20),
                    `user_id` int(20),
                    PRIMARY KEY (`id`)
                ) $charset_collate;
                CREATE TABLE {$wpdb->prefix}woocommerce_pos_drawer_transaction (
                    `t_id` int(11) NOT NULL auto_increment,
                    `outlet_id` int(11) NOT NULL,
                    `card_amount` float NOT NULL,
                    `cash_amount` float NOT NULL,
                    `opening_amount` float NOT NULL,
                    `closing_amount` float NOT NULL,
                    `date` date NOT NULL,
                    `remark` varchar(200),
                    PRIMARY KEY (`t_id`)
                ) $charset_collate;
                CREATE TABLE {$wpdb->prefix}woocommerce_pos_outlet_product_map (
                    `id` int(11) NOT NULL auto_increment,
                    `outlet_id` int(11) NOT NULL,
                    `product_id` int(11) NOT NULL,
                    `pos_status` varchar(30) NOT NULL,
                    `pos_stock` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                ) $charset_collate;";
			dbDelta( $sql );

			$sql = "CREATE TABLE {$wpdb->prefix}woocommerce_pos_payments (
                `id` int(20) NOT NULL auto_increment,
                `payment_name` varchar(100) NOT NULL,
                `payment_description` LONGTEXT NOT NULL,
                `payment_status` varchar(20) NOT NULL,
                `payment_slug` varchar(100) NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";

			dbDelta( $sql );

			$sql = "CREATE TABLE {$wpdb->prefix}woocommerce_pos_invoice_templates (
                `id` bigint(20) NOT NULL auto_increment,
                `name` varchar(200) NOT NULL,
                `invoice_html` LONGTEXT NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
                `modified_at` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) $charset_collate;";

			dbDelta( $sql );

			$woocommerce_pos_order_stats_table_name = $wpdb->prefix . 'woocommerce_pos_order_stats';

			$max_index_length = 191;

			$sql = "CREATE TABLE IF NOT EXISTS $woocommerce_pos_order_stats_table_name (
            order_id bigint(20) unsigned NOT NULL,
            parent_id bigint(20) unsigned DEFAULT 0 NOT NULL,
            date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            date_created_gmt datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            num_items_sold int(11) DEFAULT 0 NOT NULL,
            total_sales double DEFAULT 0 NOT NULL,
            tax_total double DEFAULT 0 NOT NULL,
            shipping_total double DEFAULT 0 NOT NULL,
            net_total double DEFAULT 0 NOT NULL,
            returning_customer boolean DEFAULT NULL,
            status varchar(200) NOT NULL,
            customer_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (order_id),
            KEY date_created (date_created),
            KEY customer_id (customer_id),
            outlet_id int(11) NOT NULL,
            KEY status (status({$max_index_length}))
            ) $charset_collate;";

			dbDelta( $sql );

			$results = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT meta_value from `{$wpdb->prefix}postmeta` WHERE meta_key = %s", 'pos_user' ), ARRAY_A );
			foreach ( $results as $value ) {
				$outlet_id = $wpdb->get_var( $wpdb->prepare( "SELECT outlet_id from `{$wpdb->prefix}woocommerce_pos_outlet_map` WHERE user_id = %d", $value['meta_value'] ) );

				if ( $outlet_id ) {
					$wpdb->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}postmeta` SET `meta_key`=%s,`meta_value`=%s WHERE meta_key = %s AND `meta_value`=%s", '_wk_wc_pos_outlet', $outlet_id, 'pos_user', $value['meta_value'] ) );
				}
			}

			$row = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_pos_drawer_transaction LIKE 'outlet_id'" );

			if ( empty( $row ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_pos_drawer_transaction ADD outlet_id int(11)" );

				$results = $wpdb->get_results( "SELECT DISTINCT user_id from {$wpdb->prefix}woocommerce_pos_drawer_transaction", ARRAY_A );

				foreach ( $results as $value ) {
					$outlet_id = $wpdb->get_var( $wpdb->prepare( "SELECT outlet_id from {$wpdb->prefix}woocommerce_pos_outlet_map WHERE user_id = %d", $value['user_id'] ) );

					if ( $outlet_id ) {
						$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}woocommerce_pos_drawer_transaction SET outlet_id=%d WHERE user_id=%d", $outlet_id, $value['user_id'] ) );
					}
				}

				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_pos_drawer_transaction DROP COLUMN user_id" );
			}

			$outlet_row = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_pos_outlets LIKE 'outlet_payment'" );

			if ( empty( $outlet_row ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_pos_outlets ADD outlet_payment longtext NOT NULL" );
			}

			$outlet_row = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_pos_outlets LIKE 'outlet_invoice'" );

			if ( empty( $outlet_row ) ) {
				$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_pos_outlets ADD outlet_invoice bigint(20) NOT NULL" );
			}

			$outlet_row = $wpdb->get_results( "SHOW COLUMNS FROM {$wpdb->prefix}woocommerce_pos_outlet_map LIKE 'id'" );

			if ( empty( $outlet_row ) ) {

				$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_pos_outlet_map", ARRAY_A );

				$wpdb->query( "DROP TABLE {$wpdb->prefix}woocommerce_pos_outlet_map" );

				dbDelta(
					"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_pos_outlet_map (
                    `id` int(20) NOT NULL auto_increment,
                    `outlet_id` int(20),
                    `user_id` int(20),
                    PRIMARY KEY (`id`)
                ) $charset_collate;"
				);

				if ( ! empty( $results ) ) {

					foreach ( $results as $key => $value ) {

						$wpdb->insert(
							$wpdb->prefix . 'woocommerce_pos_outlet_map',
							array(
								'outlet_id' => $value['outlet_id'],
								'user_id'   => $value['user_id'],
							),
							array(
								'%d',
								'%d',
							)
						);

					}
				}
			}

		}

		public function wkwcpos_flush_permalinks() {
			global $wp_rewrite;

			update_option( 'rewrite_rules', false );
			$permalink_rule = '/%postname%/';
			$wp_rewrite->set_permalink_structure( apply_filters( 'wc_pos_modify_permalink_rule', $permalink_rule ) );
			$wp_rewrite->flush_rules( true );

			do_action( 'wc_pos_after_permalink_flush' );
		}
	}
}

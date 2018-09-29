<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if(!class_exists("MA_Available_Offers"))
{
    class MA_Available_Offers extends WP_List_Table {
        public static function get_offers( $per_page = 5, $page_number = 1, $order='desc') {
            $offset = ($page_number-1)*$per_page;
            $data = get_option("ma_fest_offers");
            $result = array();
            if(!empty($data))
            {
                $i=0;
                foreach ($data as $id => $offer) {
                    $result[$i]['id'] = $id;
                    foreach ($offer as $key => $value) {
                        $result[$i][$key] = $value;
                    }
                    $i++;
                }
            }
            asort($result,SORT_REGULAR);
            $slice = array_slice($result,$offset,$per_page);
            return $slice;
        }
        public static function record_count() {
            $data = get_option("ma_fest_offers");
            return count($data);
        }
        public function no_items() {
            _e( 'No Offers Available.', 'ma_offers_zone' );
        }
        function column_oname( $item ) {
            return stripslashes($item['name']).'<br>
                    <a href="'.admin_url("admin.php?page=ma_offers_products&edit=".$item['id']).'" id="'.$item['id'].'">Edit</a> | 
                    <a href="'.admin_url("admin.php?page=ma_offers_products&delete=".$item['id']).'" id="'.$item['id'].'">Delete</a> | 
                    <a href="'.admin_url("admin.php?page=ma_offers_products&view=".$item['id']).'" id="'.$item['id'].'">View</a>
                    ';
        }
        function column_date( $item ) {
            return "Start : ".date(get_option("links_updated_date_format"), strtotime($item['start']))."<br>End : ".date(get_option("links_updated_date_format"), strtotime($item['end']));
        }
        function column_prop($item) {
            return "Banner : <a href='".$item['banner']."' title='".$item['banner']."' target='_blank'>".(strlen($item['banner']) > 20 ? substr($item['banner'],0,20)."..." : $item['banner'])."</a><br>Badge : ".stripslashes($item['tag']);
        }
        function column_on($item) {
            return "Offer on : ".ucfirst($item['on'])."<br>".ucfirst($item['on'])."  : ".$item['ids'];
        }
        function column_by($item) {
            return "Offer by : ".ucfirst($item['by'])."<br>".ucfirst($item['by'])." : ".$item['unit'];
        }
        function column_status($item) {
            $button = '';
            if($item['status']==='active')
            {
               $button = '<span class="button button-default ma_deactive_offer" id="'.$item['id'].'">Deactivate</span>';
            }  
            else
            {
                if($item['status'] === 'force_deactive')
                {
                    $button = '<span class="button button-default ma_active_offer" id="'.$item['id'].'">Activate</span>';
                }
                else
                {
                    $button = '<span>Invisible</span>';
                }
            }
            return $button;
        }
        function get_columns() {
            $columns = [
                'oname'     => __( 'Name', 'ma_offers_zone' ),
                'date'      => __( 'Date', 'ma_offers_zone' ),
                'prop'      => __( 'Ad-Props', 'ma_offers_zone' ),
                'on'        => __( 'Offer on', 'ma_offers_zone' ),
                'by'        => __( 'Offer By', 'ma_offers_zone' ),
                'status'    => __( 'Status', 'ma_offers_zone' ),
            ];

            return $columns;
        }
        public function prepare_items() {
            $this->_column_headers = $this->get_column_info();
            $per_page     = $this->get_items_per_page( 'results_per_page', 5 );
            $current_page = $this->get_pagenum();
            $total_items  = self::record_count();
            $this->set_pagination_args( [
                    'total_items' => $total_items,
                    'per_page'    => $per_page
            ] );
            $data = self::get_offers($per_page,$current_page);
            $this->items = $data;
        }
    }
}
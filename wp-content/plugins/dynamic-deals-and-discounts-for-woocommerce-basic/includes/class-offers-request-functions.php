<?php
if (!defined('ABSPATH')) {
    exit;
}

class MA_Offer_Ajax_Requests {

    static function ma_offer_add_new() {
        $start = sanitize_text_field($_POST['start']);
        $end = sanitize_text_field($_POST['end']);
        $avail_of = get_option("ma_fest_offers",array());
        $ids = array_keys($avail_of);
        $overlapped = array();
        foreach ($ids as $id) {
            $current = ma_get_offer_data($id);
            $overlap = ma_datesOverlap($start,$end,$current['start'],$current['end']);
            if($overlap!==0)
            {
                array_push($overlapped, $current['name']);
            }
        }
        if(!empty($overlapped))
        {
            $message = (count($overlapped)<3)? implode(", ", $overlapped)." Overlapped":'More than 2 offers Overlapped';
            die(json_encode(array('status'=>'failure','message'=>$message)));
        }
        $offer = array
                (
                    'name'                      => sanitize_text_field($_POST['name']),
                    'start'                     => $start,
                    'end'                       => $end,
                    'banner'                    => sanitize_text_field($_POST['banner']),
                    'tag'                       => sanitize_text_field($_POST['tag']),
                    'discount_badge'            => sanitize_text_field($_POST['discount_t']),
                    'discount_badge_position'   => sanitize_text_field($_POST['discount_p']),
                    'on'                        => sanitize_text_field($_POST['offer_on']),
                    'ids'                       => sanitize_text_field($_POST['ids']),
                    'by'                        => sanitize_text_field($_POST['discount_by']),
                    'unit'                      => sanitize_text_field($_POST['unit']),
                    'status'                    => 'deactive'
                );
        $avail = get_option("ma_fest_offers");
        $status = false;
        do 
        {
            $slug = "ma_off_".MA_Offer_Ajax_Requests::random_slug(4);
            if(!isset($avail[$slug]))
            {
                $avail[$slug] = $offer;
                update_option("ma_fest_offers",$avail);
                $status = false;
            }
            else
            {
                $status = true;
            }
        } while ($status);
        die(json_encode(array('status'=>'success','url'=>admin_url("admin.php?page=ma_offers_products"))));
        
    }
    
    static function random_slug($size) {
        $alpha_key = '';
        $keys = range('A', 'Z');
        for ($i = 0; $i < 2; $i++) {
            $alpha_key .= $keys[array_rand($keys)];
        }
        $length = $size - 2;
        $key = '';
        $keys = range(0, 9);
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
        return $alpha_key . $key;
    }
    static function ma_offer_edit_status() {
        $id = sanitize_text_field($_POST['id']);
        $status = sanitize_text_field($_POST['status']);
        $avail = get_option("ma_fest_offers");
        if($status === 'deactive')
        {
            $avail[$id]['status'] = 'force_deactive';
            update_option("ma_fest_offers", $avail);
            die(admin_url("admin.php?page=ma_offers_products"));
        }
        else
        {
            $off = $avail[$id]['end'];
            $mod_off = strtotime($off);
            $mod_cur = strtotime(get_date_from_gmt(date('m/d/Y h:i A',time())));
            if ($mod_cur < $mod_off) {
                $avail[$id]['status'] = $status;
                update_option("ma_fest_offers", $avail);
                die(json_encode(array('status'=>'success','url'=>admin_url("admin.php?page=ma_offers_products"))));
            } else {
                $avail[$id]['status'] = "deactive";
                update_option("ma_fest_offers", $avail);
                die(json_encode(array('status'=>'failure','message'=>"OOPS! Offer already Expired.")));
            }    

        }
    }
    
    static function ma_offer_edit() {
        $id = sanitize_text_field($_POST['id']);
        $start = sanitize_text_field($_POST['start']);
        $end = sanitize_text_field($_POST['end']);
        $avail_of = get_option("ma_fest_offers");
        unset($avail_of[$id]);
        $ids = array_keys($avail_of);
        $overlapped = array();
        foreach ($ids as $cid) {
            $current = ma_get_offer_data($cid);
            $overlap = ma_datesOverlap($start,$end,$current['start'],$current['end']);
            if($overlap!==0)
            {
                array_push($overlapped, $current['name']);
            }
        }
        if(!empty($overlapped))
        {
            $message = (count($overlapped)<3)? implode(", ", $overlapped)." Overlapped":'More than 2 offers Overlapped';
            die(json_encode(array('status'=>'failure','message'=>$message)));
        }
        $offer = array
                (
                    'name'                      => sanitize_text_field($_POST['name']),
                    'start'                     => sanitize_text_field($_POST['start']),
                    'end'                       => sanitize_text_field($_POST['end']),
                    'banner'                    => sanitize_text_field($_POST['banner']),
                    'tag'                       => sanitize_text_field($_POST['tag']),
                    'discount_badge'            => sanitize_text_field($_POST['discount_t']),
                    'discount_badge_position'   => sanitize_text_field($_POST['discount_p']),
                    'on'                        => sanitize_text_field($_POST['offer_on']),
                    'ids'                       => sanitize_text_field($_POST['ids']),
                    'by'                        => sanitize_text_field($_POST['discount_by']),
                    'unit'                      => sanitize_text_field($_POST['unit']),
                    'status'                    => 'deactive'
                );
        $avail = get_option("ma_fest_offers");
        $avail[$id] = $offer;
        update_option("ma_fest_offers",$avail);
        die(json_encode(array('status'=>'success','url'=>admin_url("admin.php?page=ma_offers_products"))));
    }
}

<?php

if (!defined('ABSPATH')) {
    exit;
}

function ma_get_offer_data($id) {
    $avail = get_option("ma_fest_offers",array());
    if(isset($avail[$id]))
    {
        return $avail[$id];
    }
    else
    {
        return array();
    }
}

function ma_get_active_offers() {
    $avail = get_option("ma_fest_offers",array());
    foreach ($avail as $off_id => $off_data) {
        if ($off_data['status'] === 'active') {
            return $off_id;
        }
    }
    return "";
}
function ma_offer_get_formatted_date($date)
{
    $timeformat = get_option('date_format').' '.get_option('time_format');
    $return = ((get_option('timezone_string')!=="")?get_date_from_gmt($date, $timeformat):date_i18n($timeformat,strtotime("+".(get_option('gmt_offset')*60)." minutes", strtotime($date))));
    return $return;
}
function ma_datesOverlap($start_real, $end_real, $start_check, $end_check) {
    $mod_start_real     = strtotime($start_real);
    $mod_end_real       = strtotime($end_real);
    $mod_start_check    = strtotime($start_check);
    $mod_end_check      = strtotime($end_check);
    if ($mod_start_real <= $mod_end_check && $mod_end_real >= $mod_start_check) {
        return 1;
    }
    return 0;
}
function ma_is_product_on_offfer($offer,$id)
{
    switch ($offer['on']) {
        case "products":
            $product_ids = explode(',', $offer['ids']);
            if(in_array($id, $product_ids))
            {
                return true;
            }
            break;
        case "categories":
            $categories_ids = explode(',', $offer['ids']);
            $categories = get_the_terms( $id, 'product_cat' );
            if(is_wp_error($categories) || !$categories)
            {
                $categories = array();
            }
            $all_slug = array();
            foreach ($categories as $category) {
                array_push($all_slug, $category->slug);
            }
            $result=array_intersect($all_slug,$categories_ids);
            if(!empty($result))
            {
                return true;
            }
            break;
    }
    return false;
}

function ma_get_offer_settings($key,$default=false)
{
    return get_option($key,$default);
}

function ma_get_upcoming_offer()
{
    $upcoming = array();
    $duration = ma_get_offer_settings('ma_offers_settings_show_ad_image_duration', 'week');
    $avail = get_option("ma_fest_offers",array());
    $offers = array();
    foreach ($avail as $off_id => $off_data) {
        $offers[$off_id] = $off_data['start'];
    }
    switch ($duration) {
        case 'week':
            foreach ($offers as $id => $start) {
                if( strtotime($start) > strtotime("next week monday") && strtotime($start) < strtotime("next week sunday +1 day") ) {
                    array_push($upcoming, $id);
                }
            }
            break;
        case 'month':
            foreach ($offers as $id => $start) {
                if( strtotime($start) > strtotime("first day of next month") && strtotime($start) < strtotime("last day of next month +1 day") ) {
                    array_push($upcoming, $id);
                }
            }
            break;
        case 'year':
            foreach ($offers as $id => $start) {
                if( strtotime($start) > strtotime("first day of January".date('Y', strtotime("+1 year"))) && strtotime($start) < strtotime("first day of January".date('Y', strtotime("+2 year"))) ) {
                    array_push($upcoming, $id);
                }
            }
            break;
        case 'all':
            foreach ($offers as $id => $start) {
                if( strtotime($start) > strtotime("now")) {
                    array_push($upcoming, $id);
                }
            }
    }
    return $upcoming;
}
<?php

if (!defined('ABSPATH')) {
    exit;
}

class MA_Offers_Sync_Setup {
    protected $active;
    function __construct() {
        $this->active = ma_get_active_offers();
        $this->validate_offers();
    }
    function validate_offers()
    {
        $avail_offers = get_option("ma_fest_offers",array());
        $match = "";
        if($this->active === "")
        {
            foreach ($avail_offers as $off_key => $off_data) {
                $start  = strtotime($off_data['start']);
                $end    = strtotime($off_data['end']);
                $now    = strtotime(ma_offer_get_formatted_date(gmdate('m/d/Y h:i A',time())));
                if($now >= $start && $now <= $end && $off_data['status'] !== 'force_deactive')
                {
                    $match = $off_key;
                }
            }
            if($match!=="")
            {
                $avail_offers[$match]['status'] = 'active';
                update_option("ma_fest_offers", $avail_offers);
            }
        }
        else
        {
            $data = ma_get_offer_data($this->active);
            $end    = strtotime($data['end']);
            $now    = strtotime(ma_offer_get_formatted_date(gmdate('m/d/Y h:i A',time())));
            if($now>=$end)
            {
                $avail_offers[$this->active]['status'] = 'deactive';
                update_option("ma_fest_offers", $avail_offers);
            }
        }
    }
}
<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('WT_Sequentialordnum_free_to_pro')) :

    /**
     * Class for pro advertisement
     */

    class WT_Sequentialordnum_free_to_pro
    {

        public function __construct()
        {
            add_action('wt_sequential_goto_pro_section_bottom', array($this, 'show_banners'));

        }
        public function show_banners()
        { 
            if(isset($_GET['page']) && $_GET['page']=='wc-settings' && isset($_GET['tab']) && $_GET['tab']=='wts_settings' && ((isset($_GET['section']) && $_GET['section'] =='') || !isset($_GET['section'])))
            {
                $seq_order_logo_url=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/logo_seq.png';
                $tick=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/tick_icon_green.png';
                $crown=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/blue-crown.svg';
                $white_crown=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/white-crown.svg';
                $money_back=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/seq_moneyback.png';
                $support=WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/seq_support.png';
                $webtoffee_logo='<img src="'.WT_SEQUENCIAL_ORDNUMBER_URL.'assets/images/wt_logo.png" style="" />&nbsp;';
                ?>
                <style type="text/css">
                    .wt_seq_goto_pro{ padding: 4px 20px 4px 20px; border-radius: 15px;background:linear-gradient( 307deg, #f3f3fc 33.85%, #ffffff 86.21%);box-sizing:border-box; height:auto;}
                    .wt_seq_logo{max-width: 100px;}
                    .wt_seq_to_pro_head{ font-weight:bold; width: 27%; text-align:left; padding-left:14px; font-size:20px; line-height: 28px; color:#5237AC;}
                    .wt_seq_to_pro_head img{ float:left; border-radius:5px; margin-right:10px; }
                    .wt_seq_support{ padding: 10px 8px 8px 16px; border: 0.5px solid #5237AC;border-radius: 5px;color:#5237AC; font-weight: 700; font-size: 11px;}
                    .wt_seq_pro_features{padding-left: 16px;}
                    .wt_seq_pro_features li{  font-weight:500; float:left; font-size: 11px;}
                    .wt_seq_pro_features li b{ font-weight:700; max-width: 20%;}
                    .wt_seq_pro_features .wt_sc_icon_box{ float:left; width:30px; height:20px; color: #091E80;}
                    .wt_seq_pro_features .dashicons{ color:#6ABE45; border-radius:20px; margin-right:5px; }
                    .wt-seq-upgrade-to-pro-btn{ color:#fff; display:inline-block; text-transform:uppercase; text-decoration:none; text-align:center; font-size:13px; font-weight:bold; line-height:38px; padding:4px 15px; background:#5237AC;border-radius:5px;width: 195px;margin-top: 15px;}
                    .wt-seq-upgrade-to-pro-btn  img{ border:none; margin-right:5px;margin-top: 10px; }
                    .wt-seq-upgrade-to-pro-btn:hover{ color:#fff;box-shadow: 0px 4px 4px #a8a6d1b8;text-decoration: none;transform: translateY(1px);transition: all .01s ease;}
                    .wt-seq-upgrade-to-pro-btn:active, .wt-wt_mgdp-upgrade-to-pro-btn:focus{ color:#e0e0e0;}
                    @media screen and (max-width:768px) {
                    .wt_seq_upgrade_to_pro_bottom_banner_feature_list{ width:100%; margin:auto; }
                </style>
                <div class="wt_seq_goto_pro">
                    <table>
                        <tr>
                            <td style="max-width: 10%;">
                               <img  class="wt_seq_logo" src="<?php echo esc_url($seq_order_logo_url); ?>">
                            </td> 
                            <td class="wt_seq_to_pro_head" style="max-width: 25%; ">
                                <?php _e('Sequential Order Number for WooCommerce Pro', 'wt-woocommerce-sequential-order-numbers'); ?>
                            </td>
                            <td style="max-width: 20%;">
                                <ul>
                                    <li>
                                        <a href="https://www.webtoffee.com/product/woocommerce-sequential-order-numbers/?utm_source=free_plugin_sidebar&utm_medium=sequential_free&utm_campaign=Sequential_Order_Numbers&utm_content=<?php echo esc_attr(WT_SEQUENCIAL_ORDNUMBER_VERSION);?>" class="wt-seq-upgrade-to-pro-btn" target="_blank">
                                            <img src="<?php echo esc_url($white_crown); ?>"><?php _e('UPGRADE TO PREMIUM', 'wt-woocommerce-sequential-order-numbersr'); ?>
                                        </a> 
                                    </li>
                                    <div class="wt_seq_support">
                                     <li>
                                        <span class="wt_sc_icon_box" style="">
                                            <img src="<?php echo esc_url($money_back); ?>" style="width: 18px;vertical-align: middle;">
                                        </span>
                                        <?php _e('30 Day Money Back Guarantee', 'wt-woocommerce-sequential-order-numbers'); ?>
                                    </li>
                                    <li>
                                        <span class="wt_sc_icon_box" style="color:#5237AC;">
                                            <img src="<?php echo esc_url($support); ?>" style="width: 18px;vertical-align: middle;">
                                        </span>
                                        <?php _e('Fast and Superior Support', 'wt-woocommerce-sequential-order-numbers'); ?>
                                    </li>   
                                    </div>
                                    
                                </ul>
                            </td> 
                            <td style="max-width: 20%;">
                                <ul class="wt_seq_pro_features">
                                    <li>
                                        <span class="wt_sc_icon_box">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        </span>
                                        <b><?php _e('Add custom suffix for order numbers.', 'wt-woocommerce-sequential-order-numbers'); ?></b>
                                    </li>
                                    <li>
                                        <span class="wt_sc_icon_box">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        </span>
                                         <b><?php _e('Date suffix in order numbers.', 'wt-woocommerce-sequential-order-numbers'); ?></b>
                                    </li>
                                    <li>
                                        <span class="wt_sc_icon_box">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        </span>
                                         <b><?php _e('Auto reset sequence per month/year etc.', 'wt-woocommerce-sequential-order-numbers'); ?></b>
                                    </li>
                                </ul>
                            </td> 
                            <td style="max-width: 20%;">
                                <ul class="wt_seq_pro_features">
                                    <li>
                                        <span class="wt_sc_icon_box">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        </span>
                                         <b><?php _e('Custom sequence for free orders.','wt-woocommerce-sequential-order-numbers'); ?></b>
                                    </li>
                                    <li>
                                        <span class="wt_sc_icon_box">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        </span>
                                         <b><?php _e('More order number templates.', 'wt-woocommerce-sequential-order-numbers'); ?></b>
                                    </li>
                                    <li>
                                        <span class="wt_sc_icon_box">
                                            <span class="dashicons dashicons-yes-alt"></span>
                                        </span>
                                         <b><?php _e('Increment sequence in custom series.', 'wt-woocommerce-sequential-order-numbers'); ?></b>
                                    </li>
                                </ul>
                            </td>
                        </tr>
                    </table>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready(function($){
                        //To add class name to submit button
                        $( "p.submit" ).last().addClass("wt_seq_submit_btn");
                        $('.wt_seq_goto_pro').insertAfter($('.wt_seq_submit_btn'));
                    });
                    
                </script>           
                <?php
            }        
        }
    }
new WT_Sequentialordnum_free_to_pro();
    
endif;
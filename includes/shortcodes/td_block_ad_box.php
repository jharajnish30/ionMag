<?php
class td_block_ad_box extends td_block {

	private $atts = array();

    function render($atts, $content = null) {
	    parent::render($atts);

	    $this->atts = shortcode_atts(
            array(
                'spot_id' => '', //header / sidebar etc
                'align' => '', //align left or right in inline content,
	            'spot_title' => '',
                'custom_title' => '',
	            'el_class' => '',
            ), $atts);

	    $spot_id        = $this->atts['spot_id'];
	    $custom_title   = $this->atts['custom_title'];
	    $spot_title     = $this->atts['spot_title'];

        // rec title
        $rec_title = '';
        if(!empty($custom_title)) {
            $rec_title .= '<div class="td-block-title-wrap">';
                $rec_title .= $this->get_block_title();
                $rec_title .= $this->get_pull_down_filter();
            $rec_title .= '</div>';
        }

	    if(!empty($spot_title)) {
            $rec_title .= '<span class="td-adspot-title">' . $spot_title . '</span>';
        }

	    // For tagDiv composer add a placeholder element
        if (td_util::tdc_is_live_editor_iframe() || td_util::tdc_is_live_editor_ajax()) {

	        $ad_array = td_util::get_td_ads($spot_id);

	        // return if the ad for a specific spot id is empty
	        if (($spot_id === 'header' || $spot_id === 'footer_top') && empty($ad_array[$spot_id]['ad_code'])) {
		        return;
	        }

            // 'td_block_wrap' is to identify a tagDiv composer element at binding
            // 'tdc-placeholder-title' is to style de placeholder
            // block_uid is necessary to have a unique html template returned to the composer (without it the html change event doesn't trigger, and because of this the loader image is still preset)
            //
            return  '<div class="td_block_wrap td-spot-id-' . $spot_id . ' ' . $this->block_uid . '_rand">' . $this->get_block_css() . $rec_title . '<div class="tdc-placeholder-title"></div></div>';
        }

        if (empty($spot_id)) {
            return;
        }

        $ad_array = td_util::get_td_ads($spot_id);

		// return if the ad for a specific spot id is empty
	    if (empty($ad_array[$spot_id]['ad_code'])) {
		    return;
	    }


	    $buffy = '';

        if (!empty($ad_array[$spot_id]['current_ad_type'])) {

            switch ($ad_array[$spot_id]['current_ad_type']) {

                case 'other':
                    //render the normal ads
                    $buffy .= $this->render_ads($ad_array[$spot_id], $atts);
                    break;

                case 'google':
                    //render the magic google ads :)
                    $buffy .= $this->render_google_ads($ad_array[$spot_id], $atts);
                    break;
            }
        }


        //print_r($ad_array);

        return $buffy;

    }


    /**
     * This function renders and returns a google ad.
     * @param $ad_array - uses an ad array of the form:
        - current_ad_type - google or other
        - ad_code - the full ad code as entered by the user
        - disable_m - disable on monitor
        - disable_tp - disable on tablet p
        - disable_p - disable on phones
        - g_data_ad_client - the google ad client id (ca-pub-etc)
        - g_data_ad_slot - the google ad slot id
     * 'm_w' => '',  // big monitor - width
    'm_h' => '',  // big monitor - height
    'tp_w' => '', // tablet_portrait width
    'tp_h' => '', // tablet_portrait height
    'p_w' => '',  // phone width
    'p_h' => ''   // phone height
     * @param $atts test
     * @return the full rendered ad
     */
    // tagDiv google responsive renderer
    // copyright 2014 tagDiv
    function render_google_ads($ad_array, $atts) {

        $spot_id = ''; //the spot id header / sidebar etc we read it from shortcode

	    $this->atts = shortcode_atts(
            array(
                'spot_id' => '', //header / sidebar etc
                'align' => '', //align left or right in inline content
                'spot_title' => '',
                'custom_title' => '',
	            'el_class' => '',
            ), $atts);

	    $spot_id        = $this->atts['spot_id'];
	    $align          = $this->atts['align'];
	    $custom_title   = $this->atts['custom_title'];
	    $spot_title     = $this->atts['spot_title'];
	    $el_class       = $this->atts['el_class'];

        // rec title
        $rec_title = '';
        if(!empty($custom_title)) {
            $rec_title .= '<div class="td-block-title-wrap">';
                $rec_title .= $this->get_block_title();
                $rec_title .= $this->get_pull_down_filter();
            $rec_title .= '</div>';
        }
        if(!empty($spot_title)) {
            $rec_title .= '<span class="td-adspot-title">' . $spot_title . '</span>';
        }

        $default_ad_sizes = array (
            'header' => array (
                'm_w' => '728',  // big monitor - width
                'm_h' => '90',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '468', // tablet_portrait width
                'tp_h' => '60', // tablet_portrait height

                'p_w' => '320',  // phone width
                'p_h' => '50'   // phone height
            ),
            'sidebar' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),


            'content_inline' => array (
                'm_w' => '468',  // big monitor - width
                'm_h' => '60',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '468', // tablet_portrait width
                'tp_h' => '60', // tablet_portrait height

                'p_w' => '320',  // phone width
                'p_h' => '50'   // phone height
            ),

            'content_top' => array (
                'm_w' => '468',  // big monitor - width
                'm_h' => '60',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '468', // tablet_portrait width
                'tp_h' => '60', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'content_bottom' => array (
                'm_w' => '468',  // big monitor - width
                'm_h' => '60',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '468', // tablet_portrait width
                'tp_h' => '60', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),


            'footer_top' => array (
	            'm_w' => '728',  // big monitor - width
	            'm_h' => '90',  // big monitor - height

	            'tl_w' => '728', // tablet_landscape width
	            'tl_h' => '90', // tablet_landscape height

	            'tp_w' => '728', // tablet_portrait width
	            'tp_h' => '90', // tablet_portrait height

	            'p_w' => '300',  // phone width
	            'p_h' => '250'   // phone height
            ),

            'custom_ad_1' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'custom_ad_2' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'custom_ad_3' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'custom_ad_4' => array (
	            'm_w' => '300',  // big monitor - width
	            'm_h' => '250',  // big monitor - height

	            'tl_w' => '300', // tablet_landscape width
	            'tl_h' => '250', // tablet_landscape height

	            'tp_w' => '200', // tablet_portrait width
	            'tp_h' => '200', // tablet_portrait height

	            'p_w' => '300',  // phone width
	            'p_h' => '250'   // phone height
            ),

            'custom_ad_5' => array (
	            'm_w' => '300',  // big monitor - width
	            'm_h' => '250',  // big monitor - height

	            'tl_w' => '300', // tablet_landscape width
	            'tl_h' => '250', // tablet_landscape height

	            'tp_w' => '200', // tablet_portrait width
	            'tp_h' => '200', // tablet_portrait height

	            'p_w' => '300',  // phone width
	            'p_h' => '250'   // phone height
            ),

            'post_style_rd_1' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'post_style_rd_9' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'post_style_rd_14' => array (
                'm_w' => '728',  // big monitor - width
                'm_h' => '90',  // big monitor - height

                'tl_w' => '728', // tablet_landscape width
                'tl_h' => '90', // tablet_landscape height

                'tp_w' => '728', // tablet_portrait width
                'tp_h' => '90', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'smart_list_rd_3' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '200', // tablet_landscape width
                'tl_h' => '200', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'smart_list_rd_4' => array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'smart_list_rd_5' => array (
                'm_w' => '468',  // big monitor - width
                'm_h' => '60',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '300', // tablet_portrait width
                'tp_h' => '250', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'smart_list_rd_6' => array (
                'm_w' => '468',  // big monitor - width
                'm_h' => '60',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '300', // tablet_portrait width
                'tp_h' => '250', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            ),

            'smart_list_rd_7' => array (
                'm_w' => '468',  // big monitor - width
                'm_h' => '60',  // big monitor - height

                'tl_w' => '468', // tablet_landscape width
                'tl_h' => '60', // tablet_landscape height

                'tp_w' => '300', // tablet_portrait width
                'tp_h' => '250', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            )
        );


        if ($align == 'left') {
            $default_ad_sizes['content_inline'] = array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            );
        }
        elseif ($align == 'right') {
            $default_ad_sizes['content_inline'] = array (
                'm_w' => '300',  // big monitor - width
                'm_h' => '250',  // big monitor - height

                'tl_w' => '300', // tablet_landscape width
                'tl_h' => '250', // tablet_landscape height

                'tp_w' => '200', // tablet_portrait width
                'tp_h' => '200', // tablet_portrait height

                'p_w' => '300',  // phone width
                'p_h' => '250'   // phone height
            );
        }







        //overwrite the default values if we have some

        //monitor big ad
        if (!empty($ad_array['m_size'])) {
            $ad_size_parts = explode(' x ', $ad_array['m_size']);
            $default_ad_sizes[$spot_id]['m_w'] = $ad_size_parts[0];
            $default_ad_sizes[$spot_id]['m_h'] = $ad_size_parts[1];
        }


	    //tablet landscape
	    if (!empty($ad_array['tl_size'])) {
		    $ad_size_parts = explode(' x ', $ad_array['tl_size']);
		    $default_ad_sizes[$spot_id]['tl_w'] = $ad_size_parts[0];
		    $default_ad_sizes[$spot_id]['tl_h'] = $ad_size_parts[1];
	    }


        //tablet portrait
        if (!empty($ad_array['tp_size'])) {
            $ad_size_parts = explode(' x ', $ad_array['tp_size']);
            $default_ad_sizes[$spot_id]['tp_w'] = $ad_size_parts[0];
            $default_ad_sizes[$spot_id]['tp_h'] = $ad_size_parts[1];
        }


        //phone
        if (!empty($ad_array['p_size'])) {
            $ad_size_parts = explode(' x ', $ad_array['p_size']);
            $default_ad_sizes[$spot_id]['p_w'] = $ad_size_parts[0];
            $default_ad_sizes[$spot_id]['p_h'] = $ad_size_parts[1];
        }





        //init the disable variables
        if (!empty($ad_array['disable_m']) and $ad_array['disable_m'] == 'yes') {
            $default_ad_sizes[$spot_id]['disable_m'] = true;
        } else {
            $default_ad_sizes[$spot_id]['disable_m'] = false;
        }

	    if (!empty($ad_array['disable_tl']) and $ad_array['disable_tl'] == 'yes') {
		    $default_ad_sizes[$spot_id]['disable_tl'] = true;
	    } else {
		    $default_ad_sizes[$spot_id]['disable_tl'] = false;
	    }

        if (!empty($ad_array['disable_tp']) and $ad_array['disable_tp'] == 'yes') {
            $default_ad_sizes[$spot_id]['disable_tp'] = true;
        } else {
            $default_ad_sizes[$spot_id]['disable_tp'] = false;
        }

        if (!empty($ad_array['disable_p']) and $ad_array['disable_p'] == 'yes') {
            $default_ad_sizes[$spot_id]['disable_p'] = true;
        } else {
            $default_ad_sizes[$spot_id]['disable_p'] = false;
        }




        $buffy = "\n <!-- A generated by theme --> \n\n";

        //google async script
        $buffy .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';




        $buffy .= '<div class="td-g-rec td-g-rec-id-' . $spot_id . $align . ' ' . $this->get_block_classes() . ' ' . $el_class . '">' . "\n";

            //get the block css
            $buffy .= $this->get_block_css();

            $buffy .= '<script type="text/javascript">' . "\n";

	        //fix for adsense custom ad size settings not loading right when having the speedbooster active
            $buffy .= 'var td_screen_width = window.innerWidth;' . "\n";


            if ($default_ad_sizes[$spot_id]['disable_m'] == false and !empty($default_ad_sizes[$spot_id]['m_w']) and !empty($default_ad_sizes[$spot_id]['m_h'])) {
                $buffy .= '
                    if ( td_screen_width >= 1140 ) {
                        /* large monitors */
                        document.write(\'' . $rec_title . '<ins class="adsbygoogle" style="display:inline-block;width:' . $default_ad_sizes[$spot_id]['m_w'] . 'px;height:' . $default_ad_sizes[$spot_id]['m_h'] . 'px" data-ad-client="' . $ad_array['g_data_ad_client'] . '" data-ad-slot="' . $ad_array['g_data_ad_slot'] . '"></ins>\');
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    }
            ';
            }


		    if ($default_ad_sizes[$spot_id]['disable_tl'] == false and !empty($default_ad_sizes[$spot_id]['tl_w']) and !empty($default_ad_sizes[$spot_id]['tl_h'])) {
			    $buffy .= '
	                    if ( td_screen_width >= 1019  && td_screen_width < 1140 ) {
	                        /* landscape tablets */
                        document.write(\'' . $rec_title . '<ins class="adsbygoogle" style="display:inline-block;width:' . $default_ad_sizes[$spot_id]['tl_w'] . 'px;height:' . $default_ad_sizes[$spot_id]['tl_h'] . 'px" data-ad-client="' . $ad_array['g_data_ad_client'] . '" data-ad-slot="' . $ad_array['g_data_ad_slot'] . '"></ins>\');
	                        (adsbygoogle = window.adsbygoogle || []).push({});
	                    }
	                ';
		    }


            if ($default_ad_sizes[$spot_id]['disable_tp'] == false and !empty($default_ad_sizes[$spot_id]['tp_w']) and !empty($default_ad_sizes[$spot_id]['tp_h'])) {
                $buffy .= '
                    if ( td_screen_width >= 768  && td_screen_width < 1019 ) {
                        /* portrait tablets */
                        document.write(\'' . $rec_title . '<ins class="adsbygoogle" style="display:inline-block;width:' . $default_ad_sizes[$spot_id]['tp_w'] . 'px;height:' . $default_ad_sizes[$spot_id]['tp_h'] . 'px" data-ad-client="' . $ad_array['g_data_ad_client'] . '" data-ad-slot="' . $ad_array['g_data_ad_slot'] . '"></ins>\');
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    }
                ';
            }

            if ($default_ad_sizes[$spot_id]['disable_p'] == false and !empty($default_ad_sizes[$spot_id]['p_w']) and !empty($default_ad_sizes[$spot_id]['p_h'])) {
                $buffy .= '
                    if ( td_screen_width < 768 ) {
                        /* Phones */
                        document.write(\'' . $rec_title . '<ins class="adsbygoogle" style="display:inline-block;width:' . $default_ad_sizes[$spot_id]['p_w'] . 'px;height:' . $default_ad_sizes[$spot_id]['p_h'] . 'px" data-ad-client="' . $ad_array['g_data_ad_client'] . '" data-ad-slot="' . $ad_array['g_data_ad_slot'] . '"></ins>\');
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    }
                ';
            }


            //$buffy .= 'console.log(td_a_g_custom_size)';

            $buffy .= '</script>' . "\n";

        $buffy .= '</div>' . "\n";
        $buffy .= "\n <!-- end A --> \n\n";
        return $buffy;
    }


    /**
     * This function renders and returns a normal ad.
     * @param $ad_array - uses an ad array of the form:
    - current_ad_type - google or other
    - ad_code - the full ad code as entered by the user
    - disable_m - disable on monitor
    - disable_tp - disable on tablet p
    - disable_p - disable on phones
    - g_data_ad_client - the google ad client id (ca-pub-etc)
    - g_data_ad_slot - the google ad slot id
     *
     * @return the full rendered ad
     */
    function render_ads($ad_array, $atts) {

        $spot_id = ''; //the spot id header / sidebar etc we read it from shortcode

        $this->atts = shortcode_atts(
            array(
                'spot_id' => '', //header / sidebar etc
                'align' => '', //align left or right in inline content
                'spot_title' => '',
                'custom_title' => '',
	            'el_class' => '',
            ), $atts);

	    $spot_id        = $this->atts['spot_id'];
	    $align          = $this->atts['align'];
	    $custom_title   = $this->atts['custom_title'];
	    $spot_title     = $this->atts['spot_title'];
	    $el_class       = $this->atts['el_class'];

        // rec title
        $rec_title = '';
        if(!empty($custom_title)) {
            $rec_title .= '<div class="td-block-title-wrap">';
                $rec_title .= $this->get_block_title();
                $rec_title .= $this->get_pull_down_filter();
            $rec_title .= '</div>';
        }
        if(!empty($spot_title)) {
            $rec_title .= '<span class="td-adspot-title">' . $spot_title . '</span>';
        }


        $buffy = '';

	    $buffy .= '<div class="td-a-rec td-a-rec-id-' . $spot_id . $align
            . ((!empty($ad_array['disable_m'])) ? ' td-rec-hide-on-m' : '')
            . ((!empty($ad_array['disable_tl'])) ? ' td-rec-hide-on-tl' : '')
            . ((!empty($ad_array['disable_tp'])) ? ' td-rec-hide-on-tp' : '')
            . ((!empty($ad_array['disable_p'])) ? ' td-rec-hide-on-p' : '')
            . ' ' . $this->get_block_classes() . ' ' . $el_class . '">';

            //get the block css
            $buffy .= $this->get_block_css();

            $buffy .= $rec_title;

            $buffy .= '<div class="td-rec-wrap">';
            $buffy .= do_shortcode(stripslashes($ad_array['ad_code']));
            $buffy .= '</div>';
        $buffy .= '</div>';


        //print_r($ad_array);
        return $buffy;

    }
}
<?php
require __DIR__ . '/wp-load.php';
global $wpdb;

function convertInchToCm ($content) {
    $content = preg_replace_callback("/(\d{1,3}'')\s(x)\s(\d{1,3}''In)/", function($m) {
        $m1 = trim($m[1], "''");
        $m1 = round(2.54*$m1, 0);
        $m1 = $m1 . 'cm';

        $m2 = " {$m[2]} ";

        $m3 = trim($m[3], "''In");
        $m3 = round(2.54*$m3, 0);
        $m3 = $m3 . 'cm';

        return $m1 . $m2 . $m3;
    }, $content);

    $content = preg_replace_callback('/(\d{1,3}")/', function($m) {
        $number = trim($m[1], '"');
        $number = round(2.54*$number, 0);
        $number = $number . 'cm';
        return $number;
    }, $content);
    return $content;
}

function convertInchToCmDescription ($content) {
    $content = preg_replace_callback('/(\d{1,3}”)(x)(\d{1,3}”)/', function($m) {
        $m1 = trim($m[1], '”');
        $m1 = round(2.54*$m1, 0);
        $m1 = $m1 . 'cm';

        $m2 = " {$m[2]} ";

        $m3 = trim($m[3], '”');
        $m3 = round(2.54*$m3, 0);
        $m3 = $m3 . 'cm';

        return $m1 . $m2 . $m3;
    }, $content);

    return $content;
}

$posts = $wpdb->get_results(
    "SELECT id, post_content FROM `wp_posts` 
    WHERE post_type = 'product' and id > 0;"
);
foreach ($posts as $post) {
    $post_id = $post->id;
    $post_content = convertInchToCmDescription($post->post_content);
    $wpdb->update(
        'wp_posts',
        array( 'post_content' => $post_content ),
        array( 'id' => $post_id )
    );

    $variationsPosts = $wpdb->get_results(
        "SELECT id, post_title, post_excerpt FROM `wp_posts` WHERE post_type = 'product_variation' and post_parent = $post_id;"
    );
    foreach ($variationsPosts as $vp) {
        $vpTitle = convertInchToCm($vp->post_title);
        if ($vpTitle != '') {
            $wpdb->update(
                'wp_posts',
                array( 'post_title' => $vpTitle ),
                array( 'id' => $vp->id )
            );
        }

        $vpExcerpt = convertInchToCm($vp->post_excerpt);
        if ($vpExcerpt != '') {
            $wpdb->update(
                'wp_posts',
                array( 'post_excerpt' => $vpExcerpt ),
                array( 'id' => $vp->id )
            );
        }
    }

    $postsmeta = $wpdb->get_results("
        SELECT * FROM `wp_postmeta` 
        WHERE `post_id` = $post_id
    ");

    $product = $updatedProduct = array();
    foreach ( $postsmeta as $pm ) {
        if ( $pm->meta_key == '_product_attributes' ) {
            $product['_product_attributes'] = $pm->meta_value;
        } elseif ( $pm->meta_key == '_edit_lock' ) {
            $product['_edit_lock'] = $pm->meta_value;
        } elseif ( $pm->meta_key == '_edit_last' ) {
            $product['_edit_last'] = $pm->meta_value;
        }

    }

    if (isset($product['_product_attributes'])) {
        $_product_attributes = $product['_product_attributes'];
        $_product_attributes = unserialize($_product_attributes);

        $updatedProduct['_product_attributes'] = $_product_attributes;
        foreach ($_product_attributes as $k => $v) {
            $updatedProduct['_product_attributes'][$k]['value'] = convertInchToCm($v['value']);
        }
        $updatedProduct['_product_attributes'] = serialize($updatedProduct['_product_attributes']);
    }
    $updatedProduct['_edit_lock'] = time() . ":1";

    $wpdb->update(
        'wp_postmeta',
        array( 'meta_value' => $updatedProduct['_edit_lock'] ),
        array( 'post_id' => $post_id, 'meta_key' => '_edit_lock' )
    );

    if (isset($updatedProduct['_product_attributes'])) {
        $wpdb->update(
            'wp_postmeta',
            array( 'meta_value' => $updatedProduct['_product_attributes'] ),
            array( 'post_id' => $post_id, 'meta_key' => '_product_attributes' )
        );
    }

    $variations = $wpdb->get_results(
        "SELECT post_id, meta_key, meta_value FROM `wp_postmeta` where post_id IN (SELECT id FROM `wp_posts` 
    WHERE post_type = 'product_variation' and post_parent = $post_id) and meta_key like 'attribute_%';"
    );
    foreach ($variations as $variation) {
        $value = convertInchToCm($variation->meta_value);
        if ($value != '') {
            $wpdb->update(
                'wp_postmeta',
                array( 'meta_value' => $value ),
                array( 'post_id' => $variation->post_id, 'meta_key' => $variation->meta_key )
            );
        }
    }

    echo "$post_id - Done<br/>";

}

echo "<pre>";
die('DONE!');



<?php
/*
Plugin Name: WP to Grav Exporter
Description: Exports WordPress posts to a format that can be imported by Grav CMS.
Version: 1.0
Author: fx0b
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WP_to_Grav_Exporter {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'export_posts'));
    }
    
    public function add_admin_menu() {
        add_management_page(
            'WP to Grav Exporter',
            'WP to Grav Exporter',
            'manage_options',
            'wp-to-grav-exporter',
            array($this, 'create_admin_page')
        );
    }
    
    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h2>WP to Grav Exporter</h2>
            <form method="post" action="">
                <input type="hidden" name="wp_to_grav_export" value="true">
                <p>
                    <input type="submit" class="button-primary" value="Export">
                </p>
            </form>
        </div>
        <?php
    }
    
    public function export_posts() {
        if (isset($_POST['wp_to_grav_export'])) {
            $this->generate_export_files();
        }
    }
    
    private function generate_export_files() {
        $upload_dir = wp_upload_dir();
        $export_dir = trailingslashit($upload_dir['basedir']) . 'grav-export/';

        if (!is_dir($export_dir)) {
            mkdir($export_dir, 0755, true);
        }

        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type'   => 'post',
        ));

        foreach ($posts as $post) {
            $this->export_post($post, $export_dir);
        }

        $this->create_zip($export_dir);
        $this->download_zip($export_dir . 'grav-export.zip');
    }

    private function export_post($post, $export_dir) {
        $post_dir = $export_dir . sanitize_title($post->post_title) . '/';
        
        if (!is_dir($post_dir)) {
            mkdir($post_dir, 0755, true);
        }

        $meta = array(
            'title' => $post->post_title,
            'date' => $post->post_date,
            'categories' => wp_get_post_categories($post->ID, array('fields' => 'names')),
            'tags' => wp_get_post_tags($post->ID, array('fields' => 'names')),
        );

        $meta_file = $post_dir . 'post.md';
        $content_file = $post_dir . 'default.md';

        file_put_contents($meta_file, $this->generate_yaml_front_matter($meta));
        file_put_contents($content_file, $post->post_content);
    }

    private function generate_yaml_front_matter($meta) {
        $yaml = "---\n";
        foreach ($meta as $key => $value) {
            if (is_array($value)) {
                $yaml .= "$key:\n";
                foreach ($value as $item) {
                    $yaml .= "  - $item\n";
                }
            } else {
                $yaml .= "$key: $value\n";
            }
        }
        $yaml .= "---\n";
        return $yaml;
    }

    private function create_zip($export_dir) {
        $zip = new ZipArchive();
        $zip_file = $export_dir . 'grav-export.zip';

        if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($export_dir), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $file_path = $file->getRealPath();
                    $relative_path = substr($file_path, strlen($export_dir));

                    $zip->addFile($file_path, $relative_path);
                }
            }

            $zip->close();
        }
    }

    private function download_zip($zip_file) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . basename($zip_file));
        header('Content-Length: ' . filesize($zip_file));

        flush();
        readfile($zip_file);
        unlink($zip_file);
        exit;
    }
}

new WP_to_Grav_Exporter();

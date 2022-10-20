<?php

new SurveyKudir();

class SurveyKudir
{
    private $columns = [
        'q1' => 'Ведете КУДиР?',
        'q2' => 'Как вы ведёте КУДиР?',
    ];
    private $surveyName = '';
    private $surveyNameInAdmin = '';

    function __construct()
    {
        $this->surveyName = $this->fromCamelCase(static::class);
        $this->surveyNameInAdmin = $this->fromCamelCase(static::class);

        add_action('init', [$this, 'registerCustomPostType']);
        add_action('rest_api_init', [$this, 'registerApiRoute']);
        add_filter("manage_{$this->surveyName}_posts_columns", [$this, 'setCustomEditSurveyColumns']);
        add_action("manage_{$this->surveyName}_posts_custom_column", [$this, 'customSurveyColumn'], 10, 2);
        add_action('manage_posts_extra_tablenav', [$this, 'addExportButton'], 20, 1);
        add_action('init', [$this, 'exportAllSurvey']);
    }

    function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }

    function registerApiRoute()
    {
        register_rest_route('send_survey', $this->surveyName, array(
            'methods' => 'post',
            'callback' => [$this, 'apiCallback'],
            'permission_callback' => '__return_true',
        ));
    }

    function apiCallback()
    {
        $marking = $_REQUEST['marking'];
        $identityId = $_REQUEST['identityId'];
        $postId = isset($_REQUEST['postId']) ? $_REQUEST['postId'] : null;
        $wpPostId = 0;

        if (empty($postId)) {
            $new_post = array(
                'ID' => '',
                'post_title' => $identityId,
                'post_status' => 'draft',
                'post_type' => $this->surveyName,
            );
            $wpPostId = wp_insert_post($new_post);
        } else {
            $new_post = array(
                'ID' => $postId,
                'post_title' => $identityId,
            );
            $wpPostId = wp_update_post($new_post);
        }

        foreach ($marking as $key => $value) {
            update_post_meta($wpPostId, $key, $value);
        }

        return $wpPostId;
    }

    function registerCustomPostType()
    {
        register_post_type($this->surveyName, array(
            'label'               => "База {$this->surveyNameInAdmin}",
            'labels'              => array(
                'name'          => "База {$this->surveyNameInAdmin}",
                'singular_name' => "База {$this->surveyNameInAdmin}",
                'menu_name'     => "База {$this->surveyNameInAdmin}",
                'all_items'     => "Все {$this->surveyNameInAdmin}",
                'add_new'       => "Добавить {$this->surveyNameInAdmin}",
                'add_new_item'  => "Добавить новый {$this->surveyNameInAdmin}",
                'edit'          => "Редактировать",
                'edit_item'     => "Редактировать {$this->surveyNameInAdmin}",
                'new_item'      => "Новый {$this->surveyNameInAdmin}",
            ),
            'description'         => '',
            'public'              => false,
            'publicly_queryable'  => false,
            'query_var'           => false,
            'rewrite'             => false,
            'show_in_nav_menus'   => false,
            'has_archive'         => false,
            'show_ui'             => true,
            'show_in_rest'        => false,
            'rest_base'           => '',
            'show_in_menu'        => true,
            'menu_position'       => 13,
            'menu_icon'           => 'dashicons-buddicons-pm',
            'exclude_from_search' => true,
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'hierarchical'        => false,
            'supports'            => array('title'),
            'taxonomies'          => array(),
        ));
    }

    function setCustomEditSurveyColumns($columns)
    {
        unset($columns['author']);
        $columns['title'] = 'ID';

        return array_merge($columns, $this->columns);
    }

    function customSurveyColumn($column, $post_id)
    {
        echo get_post_meta($post_id, $column, true);
    }

    function addExportButton($which)
    {
        global $typenow;

        if ($this->surveyName === $typenow && 'top' === $which) {
            echo '<input type="submit" name="export_all_' . $this->surveyName . '" class="button button-primary" value="Экспорт в CSV" />';
        }
    }

    function exportAllSurvey()
    {
        if (isset($_GET["export_all_{$this->surveyName}"])) {
            $arg = array(
                'post_type' => $this->surveyName,
                'post_status' => 'any',
                'posts_per_page' => -1,
            );

            global $post;
            $arr_post = get_posts($arg);
            if ($arr_post) {
                header('Content-Encoding: UTF-8');
                header('Content-type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $this->surveyName . '_' . date('Y-m-d_H-i-s') . '.csv"');
                header('Pragma: no-cache');
                header('Expires: 0');
                echo "\xEF\xBB\xBF"; // UTF-8 BOM

                $file = fopen('php://output', 'w');
                $cols = ['ID', 'Дата'];

                foreach ($this->columns as $key => $value) {
                    array_push($cols, $value);
                }
                fputcsv($file, $cols, '|');

                foreach ($arr_post as $post) {
                    setup_postdata($post);

                    $colsValue = [
                        get_the_title(),
                        get_the_date(),
                    ];
                    foreach ($this->columns as $key => $value) {
                        array_push($colsValue, get_post_meta($post->ID, $key, true));
                    }
                    fputcsv($file, $colsValue, '|');
                }

                exit();
            }
        }
    }
}

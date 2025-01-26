<?php
define('PLUGIN_TICKETPRICE_VERSION', '1.0.0');

function plugin_init_ticketprice()
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['ticketprice'] = true;
    $PLUGIN_HOOKS['post_item_form']['ticketprice'] = 'plugin_ticketprice_item_form';
    $PLUGIN_HOOKS['item_update']['ticketprice'] = 'plugin_ticketprice_item_update';
    $PLUGIN_HOOKS['post_show_item']['ticketprice'] = 'plugin_ticketprice_display_solved_ticket';

}

function plugin_version_ticketprice()
{
    return [
        'name'           => 'Ticket Price',
        'version'        => PLUGIN_TICKETPRICE_VERSION,
        'author'         => 'Matheus Calixto',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://example.com',
        'requirements'   => [
            'glpi' => [
                'min' => '10.0'
            ]
        ]
    ];
}

function plugin_ticketprice_check_prerequisites()
{
    if (version_compare(GLPI_VERSION, '10.0', 'lt')) {
        echo "Este plugin requer GLPI >= 10.0";
        return false;
    }
    return true;
}

function plugin_ticketprice_check_config()
{
    if (true) {
        return true;
    }
    if ($verbose) {
        echo 'Installed / not configured';
    }
    return false;
}

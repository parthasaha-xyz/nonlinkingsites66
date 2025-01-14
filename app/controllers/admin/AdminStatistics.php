<?php
/*
 * @copyright Copyright (c) 2023 AltumCode (https://altumcode.com/)
 *
 * This software is exclusively sold through https://altumcode.com/ by the AltumCode author.
 * Downloading this product from any other sources and running it without a proper license is illegal,
 *  except the official ones linked from https://altumcode.com/.
 */

namespace Altum\Controllers;

use Altum\Title;

class AdminStatistics extends Controller {
    public $type;
    public $datetime;

    public function index() {

        $this->type = isset($this->params[0]) && method_exists($this, $this->params[0]) ? input_clean($this->params[0]) : 'growth';

        $this->datetime = \Altum\Date::get_start_end_dates_new();

        /* Process only data that is needed for that specific page */
        $type_data = $this->{$this->type}();

        /* Set a custom title */
        $dynamic_title = l('admin_statistics.' . $this->type . '.header', null, true) ?? l('admin_' . $this->type . '.title');
        Title::set(sprintf(l('admin_statistics.title'), $dynamic_title));

        /* Main View */
        $data = [
            'type' => $this->type,
            'datetime' => $this->datetime
        ];
        $data = array_merge($data, $type_data);

        $view = new \Altum\View('admin/statistics/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

    protected function database() {
        //ALTUMCODE:DEMO if(DEMO) { \Altum\Alerts::add_error('This command is blocked on the demo.'); redirect('admin/statistics'); };

        /* Database details */
        $database_name = DATABASE_NAME;
        $tables = [];
        $result = database()->query("
            SELECT
                TABLE_NAME AS `table`,
                ROUND((DATA_LENGTH + INDEX_LENGTH)) AS `bytes`,
                TABLE_ROWS as 'rows'
            FROM
                information_schema.TABLES
            WHERE
                TABLE_SCHEMA = '{$database_name}'
            ORDER BY
                (DATA_LENGTH + INDEX_LENGTH)
            DESC;
        ");
        while($row = $result->fetch_object()) {

            $tables[] = $row;

        }

        return [
            'tables' => $tables,
        ];
    }

    protected function growth() {

        $total = ['users' => 0, 'users_logs' => 0];

        /* Users */
        $users_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `users`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $users_chart[$row->formatted_date] = [
                'users' => $row->total
            ];

            $total['users'] += $row->total;
        }

        $users_chart = get_chart_data($users_chart);

        /* Users logs */
        $users_logs_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(DISTINCT `user_id`) AS `total`,
                 DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `users_logs`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $users_logs_chart[$row->formatted_date] = [
                'users_logs' => $row->total
            ];

            $total['users_logs'] += $row->total;
        }

        $users_logs_chart = get_chart_data($users_logs_chart);

        return [
            'total' => $total,
            'users_chart' => $users_chart,
            'users_logs_chart' => $users_logs_chart,
        ];
    }

    protected function users() {

        $total = ['continents' => 0, 'countries' => 0, 'sources' => 0, 'plans' => 0];

        /* Continents */
        $continents = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `continent_code`
            FROM
                 `users`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `continent_code`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $continents[$row->continent_code] = $row->total;
            $total['continents'] += $row->total;
        }

        /* Countries */
        $countries_map = [];
        $countries = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `country`
            FROM
                 `users`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `country`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $countries[$row->country] = $row->total;
            $countries_map[$row->country] = ['users' => $row->total];
            $total['countries'] += $row->total;
        }

        /* Sources */
        $sources = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `source`
            FROM
                 `users`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `source`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $sources[$row->source] = $row->total;
            $total['sources'] += $row->total;
        }

        /* Plans */
        $plans = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 `plan_id`
            FROM
                 `users`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `plan_id`
            ORDER BY
                `total` DESC
        ");
        while($row = $result->fetch_object()) {
            $plans[$row->plan_id] = $row->total;
            $total['plans'] += $row->total;
        }

        return [
            'continents' => $continents,
            'countries' => $countries,
            'countries_map' => $countries_map,
            'sources' => $sources,
            'plans' => $plans,
            'total' => $total,
        ];
    }

    protected function payments() {

        $total = ['total_amount' => 0, 'total_payments' => 0];

        $payments_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_payments`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`total_amount_default_currency`), 2) AS `total_amount` FROM `payments` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $payments_chart[$row->formatted_date] = [
                'total_amount' => $row->total_amount,
                'total_payments' => $row->total_payments
            ];

            $total['total_amount'] += $row->total_amount;
            $total['total_payments'] += $row->total_payments;
        }

        $payments_chart = get_chart_data($payments_chart);

        return [
            'total' => $total,
            'payments_chart' => $payments_chart
        ];

    }

    protected function redeemed_codes() {

        $total = ['discount_codes' => 0, 'redeemable_codes' => 0];

        $chart = [];
        $result = database()->query("SELECT `type`, COUNT(`type`) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `redeemed_codes` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`, `type`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            if(isset($chart[$row->formatted_date])) {
                $chart[$row->formatted_date] = [
                    'discount' => $row->type == 'discount' ? $chart[$row->formatted_date]['discount'] + $row->total : $chart[$row->formatted_date]['discount'],
                    'redeemable' => $row->type == 'redeemable' ? $chart[$row->formatted_date]['redeemable'] + $row->total : $chart[$row->formatted_date]['redeemable'],
                ];
            } else {
                $chart[$row->formatted_date] = [
                    'discount' => $row->type == 'discount' ? $row->total : 0,
                    'redeemable' => $row->type == 'redeemable' ? $row->total : 0,
                ];
            }

            $total['discount_codes'] += $row->type == 'discount' ? $row->total : 0;
            $total['redeemable_codes'] += $row->type == 'redeemable' ? $row->total : 0;
        }

        $chart = get_chart_data($chart);

        return [
            'total' => $total,
            'chart' => $chart,
        ];

    }

    protected function affiliates_commissions() {

        $total = ['amount' => 0, 'total_affiliates_commissions' => 0];

        $affiliates_commissions_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_affiliates_commissions`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`amount`), 2) AS `amount` FROM `affiliates_commissions` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $affiliates_commissions_chart[$row->formatted_date] = [
                'amount' => $row->amount,
                'total_affiliates_commissions' => $row->total_affiliates_commissions
            ];

            $total['amount'] += $row->amount;
            $total['total_affiliates_commissions'] += $row->total_affiliates_commissions;
        }

        $affiliates_commissions_chart = get_chart_data($affiliates_commissions_chart);

        return [
            'total' => $total,
            'affiliates_commissions_chart' => $affiliates_commissions_chart
        ];

    }
    protected function affiliates_withdrawals() {

        $total = ['amount' => 0, 'total_affiliates_withdrawals' => 0];

        $affiliates_withdrawals_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total_affiliates_withdrawals`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, TRUNCATE(SUM(`amount`), 2) AS `amount` FROM `affiliates_withdrawals` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $affiliates_withdrawals_chart[$row->formatted_date] = [
                'amount' => $row->amount,
                'total_affiliates_withdrawals' => $row->total_affiliates_withdrawals
            ];

            $total['amount'] += $row->amount;
            $total['total_affiliates_withdrawals'] += $row->total_affiliates_withdrawals;
        }

        $affiliates_withdrawals_chart = get_chart_data($affiliates_withdrawals_chart);

        return [
            'total' => $total,
            'affiliates_withdrawals_chart' => $affiliates_withdrawals_chart
        ];

    }

    protected function broadcasts() {

        $total = ['broadcasts' => 0, 'sent_emails' => 0];

        $broadcasts_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, SUM(`sent_emails`) AS `sent_emails` FROM `broadcasts` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $broadcasts_chart[$row->formatted_date] = [
                'broadcasts' => $row->total,
                'sent_emails' => $row->sent_emails,
            ];

            $total['broadcasts'] += $row->total;
            $total['sent_emails'] += $row->sent_emails;
        }

        $broadcasts_chart = get_chart_data($broadcasts_chart);

        return [
            'total' => $total,
            'broadcasts_chart' => $broadcasts_chart,
        ];

    }

    protected function internal_notifications() {

        $total = ['internal_notifications' => 0, 'read_notifications' => 0];

        $internal_notifications_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, SUM(`is_read`) AS `read_notifications` FROM `internal_notifications` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $internal_notifications_chart[$row->formatted_date] = [
                'internal_notifications' => $row->total,
                'read_notifications' => $row->read_notifications,
            ];

            $total['internal_notifications'] += $row->total;
            $total['read_notifications'] += $row->read_notifications;
        }

        $internal_notifications_chart = get_chart_data($internal_notifications_chart);

        return [
            'total' => $total,
            'internal_notifications_chart' => $internal_notifications_chart,
        ];

    }

    protected function push_notifications() {

        $total = ['push_notifications' => 0, 'sent_push_notifications' => 0];

        $push_notifications_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, SUM(`sent_push_notifications`) AS `sent_push_notifications` FROM `push_notifications` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $push_notifications_chart[$row->formatted_date] = [
                'push_notifications' => $row->total,
                'sent_push_notifications' => $row->sent_push_notifications,
            ];

            $total['push_notifications'] += $row->total;
            $total['sent_push_notifications'] += $row->sent_push_notifications;
        }

        $push_notifications_chart = get_chart_data($push_notifications_chart);

        return [
            'total' => $total,
            'push_notifications_chart' => $push_notifications_chart,
        ];

    }

    protected function push_subscribers() {

        $total = ['push_subscribers' => 0];

        $push_subscribers_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `push_subscribers` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $push_subscribers_chart[$row->formatted_date] = [
                'push_subscribers' => $row->total,
            ];

            $total['push_subscribers'] += $row->total;
        }

        $push_subscribers_chart = get_chart_data($push_subscribers_chart);

        return [
            'total' => $total,
            'push_subscribers_chart' => $push_subscribers_chart,
        ];

    }

    protected function teams() {

        $total = ['teams' => 0];

        $teams_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `teams` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $teams_chart[$row->formatted_date] = [
                'teams' => $row->total,
            ];

            $total['teams'] += $row->total;
        }

        $teams_chart = get_chart_data($teams_chart);

        return [
            'total' => $total,
            'teams_chart' => $teams_chart,
        ];

    }

    protected function teams_members() {

        $total = ['teams_members' => 0];

        $teams_members_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `teams_members` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $teams_members_chart[$row->formatted_date] = [
                'teams_members' => $row->total,
            ];

            $total['teams_members'] += $row->total;
        }

        $teams_members_chart = get_chart_data($teams_members_chart);

        return [
            'total' => $total,
            'teams_members_chart' => $teams_members_chart,
        ];

    }

    protected function links() {

        $total = ['links' => 0];

        $links_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `links` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' AND `type` = 'link' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $links_chart[$row->formatted_date] = [
                'links' => $row->total,
            ];

            $total['links'] += $row->total;
        }

        $links_chart = get_chart_data($links_chart);

        return [
            'total' => $total,
            'links_chart' => $links_chart,
        ];

    }

    protected function biolinks() {

        $total = ['biolinks' => 0];

        $biolinks_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `links` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' AND `type` = 'biolink' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $biolinks_chart[$row->formatted_date] = [
                'biolinks' => $row->total,
            ];

            $total['biolinks'] += $row->total;
        }

        $biolinks_chart = get_chart_data($biolinks_chart);

        return [
            'total' => $total,
            'biolinks_chart' => $biolinks_chart,
        ];

    }

    protected function files() {

        $total = ['files' => 0];

        $files_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `links` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' AND `type` = 'file' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $files_chart[$row->formatted_date] = [
                'files' => $row->total,
            ];

            $total['files'] += $row->total;
        }

        $files_chart = get_chart_data($files_chart);

        return [
            'total' => $total,
            'files_chart' => $files_chart,
        ];

    }

    protected function static() {

        $total = ['static' => 0];

        $static_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `links` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' AND `type` = 'static' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $static_chart[$row->formatted_date] = [
                'static' => $row->total,
            ];

            $total['static'] += $row->total;
        }

        $static_chart = get_chart_data($static_chart);

        return [
            'total' => $total,
            'static_chart' => $static_chart,
        ];

    }

    protected function vcards() {

        $total = ['vcards' => 0];

        $vcards_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `links` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' AND `type` = 'vcard' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $vcards_chart[$row->formatted_date] = [
                'vcards' => $row->total,
            ];

            $total['vcards'] += $row->total;
        }

        $vcards_chart = get_chart_data($vcards_chart);

        return [
            'total' => $total,
            'vcards_chart' => $vcards_chart,
        ];

    }

    protected function events() {

        $total = ['events' => 0];

        $events_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `links` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' AND `type` = 'event' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $events_chart[$row->formatted_date] = [
                'events' => $row->total,
            ];

            $total['events'] += $row->total;
        }

        $events_chart = get_chart_data($events_chart);

        return [
            'total' => $total,
            'events_chart' => $events_chart,
        ];

    }

    protected function track_links() {

        $total = ['track_links' => 0];

        $track_links_chart = [];
        $result = database()->query("
            SELECT
                 COUNT(*) AS `total`,
                 DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                 `track_links`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $track_links_chart[$row->formatted_date] = [
                'track_links' => $row->total
            ];

            $total['track_links'] += $row->total;
        }

        $track_links_chart = get_chart_data($track_links_chart);

        return [
            'total' => $total,
            'track_links_chart'   => $track_links_chart
        ];
    }

    protected function biolinks_blocks() {

        $total = [];

        $biolinks_blocks_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`, `type` FROM `biolinks_blocks` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`, `type`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            if(!array_key_exists($row->type, $biolinks_blocks_chart)) {
                $biolinks_blocks_chart[$row->type] = [];
            }

            $biolinks_blocks_chart[$row->type][$row->formatted_date] = ['total' => $row->total];

            if(!array_key_exists($row->type, $total)) {
                $total[$row->type] = $row->total;
            } else {
                $total[$row->type] += $row->total;
            }
        }

        foreach($total as $key => $value) {
            $biolinks_blocks_chart[$key] = get_chart_data($biolinks_blocks_chart[$key]);
        }

        return [
            'total' => $total,
            'biolinks_blocks_chart' => $biolinks_blocks_chart,
            'biolink_blocks' => require APP_PATH . 'includes/biolink_blocks.php',
        ];

    }

    protected function projects() {

        $total = ['projects' => 0];

        $projects_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `projects` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $projects_chart[$row->formatted_date] = [
                'projects' => $row->total,
            ];

            $total['projects'] += $row->total;
        }

        $projects_chart = get_chart_data($projects_chart);

        return [
            'total' => $total,
            'projects_chart' => $projects_chart,
        ];

    }

    protected function splash_pages() {

        $total = ['splash_pages' => 0];

        $splash_pages_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `splash_pages` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $splash_pages_chart[$row->formatted_date] = [
                'splash_pages' => $row->total,
            ];

            $total['splash_pages'] += $row->total;
        }

        $splash_pages_chart = get_chart_data($splash_pages_chart);

        return [
            'total' => $total,
            'splash_pages_chart' => $splash_pages_chart,
        ];

    }

    protected function pixels() {

        $total = ['pixels' => 0];

        $pixels_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `pixels` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $pixels_chart[$row->formatted_date] = [
                'pixels' => $row->total,
            ];

            $total['pixels'] += $row->total;
        }

        $pixels_chart = get_chart_data($pixels_chart);

        return [
            'total' => $total,
            'pixels_chart' => $pixels_chart,
        ];

    }

    protected function qr_codes() {

        $total = ['qr_codes' => 0];

        $qr_codes_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `qr_codes` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $qr_codes_chart[$row->formatted_date] = [
                'qr_codes' => $row->total,
            ];

            $total['qr_codes'] += $row->total;
        }

        $qr_codes_chart = get_chart_data($qr_codes_chart);

        return [
            'total' => $total,
            'qr_codes_chart' => $qr_codes_chart,
        ];

    }

    protected function domains() {

        $total = ['domains' => 0];

        $domains_chart = [];
        $result = database()->query("SELECT COUNT(*) AS `total`, DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date` FROM `domains` WHERE `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}' GROUP BY `formatted_date`");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $domains_chart[$row->formatted_date] = [
                'domains' => $row->total,
            ];

            $total['domains'] += $row->total;
        }

        $domains_chart = get_chart_data($domains_chart);

        return [
            'total' => $total,
            'domains_chart' => $domains_chart,
        ];

    }

    protected function signatures() {

        $total = ['signatures' => 0];

        /* Signatures */
        $signatures_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `signatures`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $signatures_chart[$row->formatted_date] = [
                'signatures' => $row->total
            ];

            $total['signatures'] += $row->total;
        }

        $signatures_chart = get_chart_data($signatures_chart);

        return [
            'total' => $total,
            'signatures_chart' => $signatures_chart,
        ];

    }

    protected function documents() {

        $total = ['documents' => 0];

        /* Documents */
        $documents_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `documents`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $documents_chart[$row->formatted_date] = [
                'documents' => $row->total
            ];

            $total['documents'] += $row->total;
        }

        $documents_chart = get_chart_data($documents_chart);

        return [
            'total' => $total,
            'documents_chart' => $documents_chart,
        ];

    }

    protected function images() {

        $total = ['images' => 0];

        /* Images */
        $images_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `images`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $images_chart[$row->formatted_date] = [
                'images' => $row->total
            ];

            $total['images'] += $row->total;
        }

        $images_chart = get_chart_data($images_chart);

        return [
            'total' => $total,
            'images_chart' => $images_chart,
        ];

    }

    protected function transcriptions() {

        $total = ['transcriptions' => 0];

        /* QR codes */
        $transcriptions_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `transcriptions`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $transcriptions_chart[$row->formatted_date] = [
                'transcriptions' => $row->total
            ];

            $total['transcriptions'] += $row->total;
        }

        $transcriptions_chart = get_chart_data($transcriptions_chart);

        return [
            'total' => $total,
            'transcriptions_chart' => $transcriptions_chart,
        ];

    }

    protected function syntheses() {

        $total = ['syntheses' => 0];

        /* Data */
        $syntheses_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `syntheses`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $syntheses_chart[$row->formatted_date] = [
                'syntheses' => $row->total
            ];

            $total['syntheses'] += $row->total;
        }

        $syntheses_chart = get_chart_data($syntheses_chart);

        return [
            'total' => $total,
            'syntheses_chart' => $syntheses_chart,
        ];

    }

    protected function chats() {

        $total = ['chats' => 0];

        /* Data */
        $chats_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `chats`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $chats_chart[$row->formatted_date] = [
                'chats' => $row->total
            ];

            $total['chats'] += $row->total;
        }

        $chats_chart = get_chart_data($chats_chart);

        return [
            'total' => $total,
            'chats_chart' => $chats_chart,
        ];

    }

    protected function chats_messages() {

        $total = ['chats_messages' => 0];

        /* Data */
        $chats_messages_chart = [];
        $result = database()->query("
            SELECT
                COUNT(*) AS `total`,
                DATE_FORMAT(`datetime`, '{$this->datetime['query_date_format']}') AS `formatted_date`
            FROM
                `chats_messages`
            WHERE
                `datetime` BETWEEN '{$this->datetime['query_start_date']}' AND '{$this->datetime['query_end_date']}'
            GROUP BY
                `formatted_date`
            ORDER BY
                `formatted_date`
        ");
        while($row = $result->fetch_object()) {
            $row->formatted_date = $this->datetime['process']($row->formatted_date);

            $chats_messages_chart[$row->formatted_date] = [
                'chats_messages' => $row->total
            ];

            $total['chats_messages'] += $row->total;
        }

        $chats_messages_chart = get_chart_data($chats_messages_chart);

        return [
            'total' => $total,
            'chats_messages_chart' => $chats_messages_chart,
        ];

    }
}

<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?php if(settings()->main->breadcrumbs_is_enabled): ?>
<nav aria-label="breadcrumb">
        <ol class="custom-breadcrumbs small">
            <li>
                <a href="<?= url('teams-system') ?>"><?= l('teams_system.breadcrumb') ?></a><i class="fas fa-fw fa-angle-right"></i>
            </li>
            <li class="active" aria-current="page"><?= l('teams_member.breadcrumb') ?></li>
        </ol>
    </nav>
<?php endif ?>

    <div class="row mb-4">
        <div class="col-12 col-xl d-flex align-items-center mb-3 mb-xl-0">
            <h1 class="h4 m-0"><i class="fas fa-fw fa-xs fa-user-tag mr-1"></i> <?= l('teams_member.header') ?></h1>

            <div class="ml-2">
                <span data-toggle="tooltip" title="<?= l('teams_member.subheader') ?>">
                    <i class="fas fa-fw fa-info-circle text-muted"></i>
                </span>
            </div>
        </div>

        <div class="col-12 col-xl-auto d-flex">
            <div>
                <div class="dropdown">
                    <button type="button" class="btn btn-light dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                        <i class="fas fa-fw fa-sm fa-download"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right d-print-none">
                        <a href="<?= url('teams-member?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                        </a>
                        <a href="<?= url('teams-member?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
                            <i class="fas fa-fw fa-sm fa-file-code mr-1"></i> <?= sprintf(l('global.export_to'), 'JSON') ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="ml-3">
                <div class="dropdown">
                    <button type="button" class="btn <?= count($data->filters->get) ? 'btn-dark' : 'btn-light' ?> filters-button dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.filters.header') ?>">
                        <i class="fas fa-fw fa-sm fa-filter"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right filters-dropdown">
                        <div class="dropdown-header d-flex justify-content-between">
                            <span class="h6 m-0"><?= l('global.filters.header') ?></span>

                            <?php if(count($data->filters->get)): ?>
                                <a href="<?= url('teams-member') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                            <?php endif ?>
                        </div>

                        <div class="dropdown-divider"></div>

                        <form action="" method="get" role="form">
                            <div class="form-group px-4">
                                <label for="search" class="small"><?= l('global.filters.search') ?></label>
                                <input type="search" name="search" id="search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                            </div>

                            <div class="form-group px-4">
                                <label for="search_by" class="small"><?= l('global.filters.search_by') ?></label>
                                <select name="search_by" id="search_by" class="custom-select custom-select-sm">
                                    <option value="name" <?= $data->filters->search_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="order_by" class="small"><?= l('global.filters.order_by') ?></label>
                                <select name="order_by" id="order_by" class="custom-select custom-select-sm">
                                    <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                    <option value="name" <?= $data->filters->order_by == 'name' ? 'selected="selected"' : null ?>><?= l('global.name') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="order_type" class="small"><?= l('global.filters.order_type') ?></label>
                                <select name="order_type" id="order_type" class="custom-select custom-select-sm">
                                    <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                    <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                                </select>
                            </div>

                            <div class="form-group px-4">
                                <label for="results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                                <select name="results_per_page" id="results_per_page" class="custom-select custom-select-sm">
                                    <?php foreach($data->filters->allowed_results_per_page as $key): ?>
                                        <option value="<?= $key ?>" <?= $data->filters->results_per_page == $key ? 'selected="selected"' : null ?>><?= $key ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>

                            <div class="form-group px-4 mt-4">
                                <button type="submit" name="submit" class="btn btn-sm btn-primary btn-block"><?= l('global.submit') ?></button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($data->teams_member)): ?>
        <div class="table-responsive table-custom-container">
            <table class="table table-custom">
                <thead>
                <tr>
                    <th><?= l('teams_member.table.team') ?></th>
                    <th><?= l('team_members.input.access') ?></th>
                    <th><?= l('global.status') ?></th>
                    <th><?= l('teams_member.table.datetime') ?></th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($data->teams_member as $row): ?>
                    <tr>
                        <td class="text-nowrap">
                            <?= $row->name ?>
                        </td>

                        <td class="text-nowrap">
                            <?php
                            $access_html = [];
                            foreach($data->teams_access as $key => $value) {
                                $access_html[$key] = '';
                                foreach ($data->teams_access[$key] as $access_key => $access_translation) {
                                    $access_html[$key] .= ($row->access->{$access_key} ? $access_translation : '<s>' . $access_translation . '</s>') . '<br />';
                                }
                            }
                            ?>
                            <span class="badge badge-secondary mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['read'] ?>">
                                <i class="fas fa-fw fa-sm fa-eye"></i> <?= l('team_members.input.access.read') ?>
                            </span>

                            <span class="badge badge-success mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['create'] ?>">
                                <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('team_members.input.access.create') ?>
                            </span>

                            <span class="badge badge-info mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['update'] ?>">
                                <i class="fas fa-fw fa-sm fa-pencil-alt"></i> <?= l('team_members.input.access.update') ?>
                            </span>

                            <span class="badge badge-danger mx-2" data-toggle="tooltip" data-html="true" title="<?= $access_html['delete'] ?>">
                                <i class="fas fa-fw fa-sm fa-trash-alt"></i> <?= l('team_members.input.access.delete') ?>
                            </span>
                        </td>

                        <td class="text-nowrap">
                            <?php if($row->status): ?>
                                <span class="badge badge-success"><?= l('team_members.table.status_accepted') ?></span>
                            <?php else: ?>
                                <span class="badge badge-warning"><?= l('team_members.table.status_invited') ?></span>
                            <?php endif ?>
                        </td>

                        <td class="text-nowrap"><span class="text-muted" data-toggle="tooltip" title="<?= \Altum\Date::get($row->datetime, 1) ?>"><?= \Altum\Date::get_timeago($row->datetime) ?></span></td>

                        <td>
                            <div class="d-flex justify-content-end">
                                <?= include_view(THEME_PATH . 'views/teams-member/teams_member_dropdown_button.php', ['id' => $row->team_member_id, 'status' => $row->status]) ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3"><?= $data->pagination ?></div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                    <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('teams_member.no_data') ?>" />
                    <h2 class="h4 text-muted"><?= l('teams_member.no_data') ?></h2>
                    <p class="text-muted"><?= l('teams_member.no_data_help') ?></p>
                </div>
            </div>
        </div>
    <?php endif ?>
</div>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/teams-member/teams_member_delete_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/teams-member/teams_member_join_modal.php'), 'modals'); ?>
<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/teams-member/teams_member_login_modal.php'), 'modals'); ?>

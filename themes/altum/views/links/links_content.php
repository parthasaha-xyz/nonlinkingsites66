<?php defined('ALTUMCODE') || die() ?>

<div class="row mb-4">
    <div class="col-12 col-lg mb-3 mb-lg-0">
        <h1 class="h4 m-0">
            <i class="fas fa-fw fa-xs <?= isset($data->filters->filters['type']) ? $data->links_types[$data->filters->filters['type']]['icon'] : $data->links_types['link']['icon'] ?> mr-1"></i>
            <?= isset($data->filters->filters['type']) ? l('links.menu.' . $data->filters->filters['type']) : l('links.header') ?>
        </h1>
    </div>

    <div class="col-12 col-lg-auto d-flex">
        <?php if(isset($data->filters->filters['type'])): ?>
            <div>
                <button type="button" data-toggle="modal" data-target="<?= '#create_' . $data->filters->filters['type'] ?>" class="btn btn-primary">
                    <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('link.' . $data->filters->filters['type'] . '.name') ?>
                </button>
            </div>

            <?php if(settings()->links->biolinks_templates_is_enabled && $data->filters->filters['type'] == 'biolink'): ?>
                <div class="ml-3">
                    <a href="<?= url('biolinks-templates') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-fw fa-moon fa-sm mr-1"></i> <?= l('biolinks_templates.menu') ?>
                    </a>
                </div>
            <?php endif ?>
        <?php else: ?>
            <div>
                <div class="dropdown">
                    <button type="button" data-toggle="dropdown" data-boundary="viewport" class="btn btn-primary dropdown-toggle dropdown-toggle-simple">
                        <i class="fas fa-fw fa-plus-circle fa-sm mr-1"></i> <?= l('links.create') ?>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right">
                        <?php if(settings()->links->biolinks_is_enabled): ?>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_biolink">
                                <i class="fas fa-fw fa-circle fa-sm mr-1" style="color: <?= $data->links_types['biolink']['color'] ?>"></i>

                                <?= l('link.biolink.name') ?>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->shortener_is_enabled): ?>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_link">
                                <i class="fas fa-fw fa-circle fa-sm mr-1" style="color: <?= $data->links_types['link']['color'] ?>"></i>

                                <?= l('link.link.name') ?>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->files_is_enabled): ?>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_file">
                                <i class="fas fa-fw fa-circle fa-sm mr-1" style="color: <?= $data->links_types['file']['color'] ?>"></i>

                                <?= l('link.file.name') ?>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->vcards_is_enabled): ?>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_vcard">
                                <i class="fas fa-fw fa-circle fa-sm mr-1" style="color: <?= $data->links_types['vcard']['color'] ?>"></i>

                                <?= l('link.vcard.name') ?>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->events_is_enabled): ?>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_event">
                                <i class="fas fa-fw fa-circle fa-sm mr-1" style="color: <?= $data->links_types['event']['color'] ?>"></i>

                                <?= l('link.event.name') ?>
                            </a>
                        <?php endif ?>

                        <?php if(settings()->links->static_is_enabled): ?>
                            <a href="#" class="dropdown-item" data-toggle="modal" data-target="#create_static">
                                <i class="fas fa-fw fa-circle fa-sm mr-1" style="color: <?= $data->links_types['static']['color'] ?>"></i>

                                <?= l('link.static.name') ?>
                            </a>
                        <?php endif ?>
                    </div>
                </div>
            </div>
        <?php endif ?>

        <div class="ml-3">
            <div class="dropdown">
                <button type="button" class="btn btn-light dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport" data-tooltip title="<?= l('global.export') ?>">
                    <i class="fas fa-fw fa-sm fa-download"></i>
                </button>

                <div class="dropdown-menu dropdown-menu-right d-print-none">
                    <a href="<?= url('links?' . $data->filters->get_get() . '&export=csv')  ?>" target="_blank" class="dropdown-item">
                        <i class="fas fa-fw fa-sm fa-file-csv mr-1"></i> <?= sprintf(l('global.export_to'), 'CSV') ?>
                    </a>
                    <a href="<?= url('links?' . $data->filters->get_get() . '&export=json') ?>" target="_blank" class="dropdown-item">
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
                            <a href="<?= url('links') ?>" class="text-muted"><?= l('global.filters.reset') ?></a>
                        <?php endif ?>
                    </div>

                    <div class="dropdown-divider"></div>

                    <form action="<?= url('links') ?>" method="get" role="form">
                        <div class="form-group px-4">
                            <label for="filters_search" class="small"><?= l('global.filters.search') ?></label>
                            <input type="search" name="search" id="filters_search" class="form-control form-control-sm" value="<?= $data->filters->search ?>" />
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_search_by" class="small"><?= l('global.filters.search_by') ?></label>
                            <select name="search_by" id="filters_search_by" class="custom-select custom-select-sm">
                                <option value="url" <?= $data->filters->search_by == 'url' ? 'selected="selected"' : null ?>><?= l('links.filters.url') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_is_enabled" class="small"><?= l('global.status') ?></label>
                            <select name="is_enabled" id="filters_is_enabled" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <option value="1" <?= isset($data->filters->filters['is_enabled']) && $data->filters->filters['is_enabled'] == '1' ? 'selected="selected"' : null ?>><?= l('global.active') ?></option>
                                <option value="0" <?= isset($data->filters->filters['is_enabled']) && $data->filters->filters['is_enabled'] == '0' ? 'selected="selected"' : null ?>><?= l('global.disabled') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <div class="d-flex justify-content-between">
                                <label for="filters_project_id" class="small"><?= l('projects.project_id') ?></label>
                                <a href="<?= url('projects') ?>" target="_blank" class="small mb-2"><i class="fas fa-fw fa-sm fa-plus mr-1"></i> <?= l('global.create') ?></a>
                            </div>
                            <select name="project_id" id="filters_project_id" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php foreach($data->projects as $row): ?>
                                    <option value="<?= $row->project_id ?>" <?= isset($data->filters->filters['project_id']) && $data->filters->filters['project_id'] == $row->project_id ? 'selected="selected"' : null ?>><?= $row->name ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_type" class="small"><?= l('global.type') ?></label>
                            <select name="type" id="filters_type" class="custom-select custom-select-sm">
                                <option value=""><?= l('global.all') ?></option>
                                <?php if(settings()->links->biolinks_is_enabled): ?>
                                    <option value="biolink" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'biolink' ? 'selected="selected"' : null ?>><?= l('links.filters.type.biolink') ?></option>
                                <?php endif ?>

                                <?php if(settings()->links->shortener_is_enabled): ?>
                                    <option value="link" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'link' ? 'selected="selected"' : null ?>><?= l('links.filters.type.link') ?></option>
                                <?php endif ?>

                                <?php if(settings()->links->files_is_enabled): ?>
                                    <option value="file" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'file' ? 'selected="selected"' : null ?>><?= l('links.filters.type.file') ?></option>
                                <?php endif ?>

                                <?php if(settings()->links->vcards_is_enabled): ?>
                                    <option value="vcard" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'vcard' ? 'selected="selected"' : null ?>><?= l('links.filters.type.vcard') ?></option>
                                <?php endif ?>

                                <?php if(settings()->links->events_is_enabled): ?>
                                    <option value="event" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'event' ? 'selected="selected"' : null ?>><?= l('links.filters.type.event') ?></option>
                                <?php endif ?>

                                <?php if(settings()->links->static_is_enabled): ?>
                                    <option value="static" <?= isset($data->filters->filters['type']) && $data->filters->filters['type'] == 'static' ? 'selected="selected"' : null ?>><?= l('links.filters.type.static') ?></option>
                                <?php endif ?>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_by" class="small"><?= l('global.filters.order_by') ?></label>
                            <select name="order_by" id="filters_order_by" class="custom-select custom-select-sm">
                                <option value="datetime" <?= $data->filters->order_by == 'datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_datetime') ?></option>
                                <option value="last_datetime" <?= $data->filters->order_by == 'last_datetime' ? 'selected="selected"' : null ?>><?= l('global.filters.order_by_last_datetime') ?></option>
                                <option value="clicks" <?= $data->filters->order_by == 'clicks' ? 'selected="selected"' : null ?>><?= l('links.filters.order_by_clicks') ?></option>
                                <option value="url" <?= $data->filters->order_by == 'url' ? 'selected="selected"' : null ?>><?= l('links.filters.url') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_order_type" class="small"><?= l('global.filters.order_type') ?></label>
                            <select name="order_type" id="filters_order_type" class="custom-select custom-select-sm">
                                <option value="ASC" <?= $data->filters->order_type == 'ASC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_asc') ?></option>
                                <option value="DESC" <?= $data->filters->order_type == 'DESC' ? 'selected="selected"' : null ?>><?= l('global.filters.order_type_desc') ?></option>
                            </select>
                        </div>

                        <div class="form-group px-4">
                            <label for="filters_results_per_page" class="small"><?= l('global.filters.results_per_page') ?></label>
                            <select name="results_per_page" id="filters_results_per_page" class="custom-select custom-select-sm">
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

<?php if(count($data->links)): ?>

    <?php foreach($data->links as $row): ?>

        <div class="custom-row mb-4 <?= $row->is_enabled ? null : 'custom-row-inactive' ?>">
            <div class="row">
                <div class="col-8 col-lg-5">
                    <div class="d-flex align-items-center">
                        <div class="mr-3 d-flex align-items-center">
                            <span class="fa-stack fa-1x" data-toggle="tooltip" title="<?= l('link.' . $row->type . '.name') ?>">
                                <i class="fas fa-circle fa-stack-2x" style="color: <?= $data->links_types[$row->type]['color'] ?>"></i>
                                <i class="<?= $data->links_types[$row->type]['icon'] ?> fa-stack-1x fa-inverse"></i>
                            </span>
                        </div>

                        <div class="d-flex flex-column min-width-0">
                            <div class="d-inline-block text-truncate">
                                <a href="<?= url('link/' . $row->link_id) ?>" class="font-weight-bold"><?= $row->url ?></a>
                                <?php if($row->type == 'biolink' && $row->is_verified): ?>
                                    <span data-toggle="tooltip" title="<?= l('link.biolink.verified') ?>"><i class="fas fa-fw fa-xs fa-check-circle" style="color: #0086ff"></i></span>
                                <?php endif ?>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="d-inline-block text-truncate">
                                <?php if(!empty($row->location_url)): ?>
                                    <img src="<?= get_favicon_url_from_domain(parse_url($row->location_url)['host']) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />
                                    <a href="<?= $row->location_url ?>" class="text-muted align-middle" target="_blank" rel="noreferrer"><?= remove_url_protocol_from_url($row->location_url) ?></a>
                                <?php else: ?>
                                    <img src="<?= get_favicon_url_from_domain(parse_url($row->full_url)['host']) ?>" class="img-fluid icon-favicon mr-1" loading="lazy" />
                                    <a href="<?= $row->full_url ?>" class="text-muted align-middle" target="_blank" rel="noreferrer"><?= remove_url_protocol_from_url($row->full_url) ?></a>
                                <?php endif ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col col-lg-3 d-none d-lg-flex flex-lg-column flex-xl-row justify-content-lg-between align-items-center">
                    <div>
                        <?php if($row->project_id && isset($data->projects[$row->project_id])): ?>
                            <a href="<?= url('links?project_id=' . $row->project_id) ?>" class="text-decoration-none">
                                <span class="badge badge-light" style="color: <?= $data->projects[$row->project_id]->color ?> !important;">
                                    <?= $data->projects[$row->project_id]->name ?>
                                </span>
                            </a>
                        <?php endif ?>
                    </div>

                    <div>
                        <a href="<?= url('link/' . $row->link_id . '/statistics') ?>">
                            <span data-toggle="tooltip" title="<?= l('links.clicks') ?>"><span class="badge badge-light"><i class="fas fa-fw fa-sm fa-chart-bar mr-1"></i> <?= nr($row->clicks) ?></span></span>
                        </a>
                    </div>
                </div>

                <div class="col col-xl-2 d-none d-xl-flex justify-content-xl-end align-items-center">
                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.datetime_tooltip'), '<br />' . \Altum\Date::get($row->datetime, 2) . '<br /><small>' . \Altum\Date::get($row->datetime, 3) . '</small>') ?>">
                        <i class="fas fa-fw fa-calendar text-muted"></i>
                    </span>

                    <span class="mr-2" data-toggle="tooltip" data-html="true" title="<?= sprintf(l('global.last_datetime_tooltip'), ($row->last_datetime ? '<br />' . \Altum\Date::get($row->last_datetime, 2) . '<br /><small>' . \Altum\Date::get($row->last_datetime, 3) . '</small>' : '-')) ?>">
                        <i class="fas fa-fw fa-history text-muted"></i>
                    </span>
                </div>

                <div class="col-4 col-lg-4 col-xl-2 d-flex justify-content-center justify-content-lg-end align-items-center">
                    <div class="custom-control custom-switch" data-toggle="tooltip" title="<?= l('links.is_enabled_tooltip') ?>">
                        <input
                            type="checkbox"
                            class="custom-control-input"
                            id="link_is_enabled_<?= $row->link_id ?>"
                            data-row-id="<?= $row->link_id ?>"
                            onchange="ajax_call_helper(event, 'link-ajax', 'is_enabled_toggle')"
                            <?= $row->is_enabled ? 'checked="checked"' : null ?>
                        >
                        <label class="custom-control-label clickable" for="link_is_enabled_<?= $row->link_id ?>"></label>
                    </div>

                    <button
                        id="url_copy"
                        type="button"
                        class="btn btn-link text-secondary"
                        data-toggle="tooltip"
                        title="<?= l('global.clipboard_copy') ?>"
                        aria-label="<?= l('global.clipboard_copy') ?>"
                        data-copy="<?= l('global.clipboard_copy') ?>"
                        data-copied="<?= l('global.clipboard_copied') ?>"
                        data-clipboard-text="<?= $row->full_url ?>"
                    >
                        <i class="fas fa-fw fa-sm fa-copy"></i>
                    </button>

                    <div class="dropdown">
                        <button type="button" class="btn btn-link text-secondary dropdown-toggle dropdown-toggle-simple" data-toggle="dropdown" data-boundary="viewport">
                            <i class="fas fa-fw fa-ellipsis-v"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right">
                            <a href="<?= url('link/' . $row->link_id) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-pencil-alt mr-2"></i> <?= l('global.edit') ?></a>
                            <a href="<?= url('link/' . $row->link_id . '/statistics') ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-chart-bar mr-2"></i> <?= l('link.statistics.link') ?></a>
                            <?php if(settings()->links->qr_codes_is_enabled): ?>
                                <a href="<?= url('qr-code-create?name=' . $row->url . '&project_id=' . $row->project_id . '&type=url&url=' . $row->full_url) ?>" class="dropdown-item"><i class="fas fa-fw fa-sm fa-qrcode mr-2"></i> <?= l('qr_codes.create') ?></a>
                            <?php endif ?>
                            <a href="#" data-toggle="modal" data-target="#link_duplicate_modal" class="dropdown-item" data-link-id="<?= $row->link_id ?>"><i class="fas fa-fw fa-sm fa-clone mr-2"></i> <?= l('global.duplicate') ?></a>
                            <a href="#" data-toggle="modal" data-target="#link_delete_modal" class="dropdown-item" data-link-id="<?= $row->link_id ?>"><i class="fas fa-fw fa-sm fa-trash-alt mr-2"></i> <?= l('global.delete') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach ?>

    <div class="mt-3"><?= $data->pagination ?></div>

<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-column align-items-center justify-content-center py-3">
                <img src="<?= ASSETS_FULL_URL . 'images/no_rows.svg' ?>" class="col-10 col-md-7 col-lg-4 mb-3" alt="<?= l('links.no_data') ?>" />
                <h2 class="h4 text-muted"><?= l('links.no_data') ?></h2>
            </div>
        </div>
    </div>
<?php endif ?>

<?php \Altum\Event::add_content(include_view(THEME_PATH . 'views/partials/duplicate_modal.php', ['modal_id' => 'link_duplicate_modal', 'resource_id' => 'link_id', 'path' => 'link-ajax/duplicate']), 'modals'); ?>
<?php include_view(THEME_PATH . 'views/partials/clipboard_js.php') ?>

<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Various table operations
 *
 * @package PhpMyAdmin
 */
use PhpMyAdmin\Index;
use PhpMyAdmin\Message;
use PhpMyAdmin\Partition;
use PhpMyAdmin\Operations;
use PhpMyAdmin\Relation;
use PhpMyAdmin\Response;
use PhpMyAdmin\Table;
use PhpMyAdmin\Util;

/**
 *
 */
require_once 'libraries/common.inc.php';

/**
 * functions implementation for this script
 */
require_once 'libraries/check_user_privileges.inc.php';

$pma_table = new Table($GLOBALS['table'], $GLOBALS['db']);

/**
 * Load JavaScript files
 */
$response = Response::getInstance();
$header   = $response->getHeader();
$scripts  = $header->getScripts();
$scripts->addFile('tbl_operations.js');

/**
 * Runs common work
 */
require 'libraries/tbl_common.inc.php';
$url_query .= '&amp;goto=tbl_operations.php&amp;back=tbl_operations.php';
$url_params['goto'] = $url_params['back'] = 'tbl_operations.php';

/**
 * Gets relation settings
 */
$relation = new Relation();
$cfgRelation = $relation->getRelationsParam();

// reselect current db (needed in some cases probably due to
// the calling of PhpMyAdmin\Relation)
$GLOBALS['dbi']->selectDb($GLOBALS['db']);

/**
 * Gets tables information
 */
$pma_table = $GLOBALS['dbi']->getTable(
    $GLOBALS['db'],
    $GLOBALS['table']
);
$reread_info = $pma_table->getStatusInfo(null, false);
$GLOBALS['showtable'] = $pma_table->getStatusInfo(null, (isset($reread_info) && $reread_info ? true : false));
if ($pma_table->isView()) {
    $tbl_is_view = true;
    $tbl_storage_engine = __('View');
    $show_comment = null;
} else {
    $tbl_is_view = false;
    $tbl_storage_engine = $pma_table->getStorageEngine();
    $show_comment = $pma_table->getComment();
}
$tbl_collation = $pma_table->getCollation();
$table_info_num_rows = $pma_table->getNumRows();
$row_format = $pma_table->getRowFormat();
$auto_increment = $pma_table->getAutoIncrement();
$create_options = $pma_table->getCreateOptions();

// set initial value of these variables, based on the current table engine
if ($pma_table->isEngine('ARIA')) {
    // the value for transactional can be implicit
    // (no create option found, in this case it means 1)
    // or explicit (option found with a value of 0 or 1)
    // ($create_options['transactional'] may have been set by Table class,
    // from the $create_options)
    $create_options['transactional'] = (isset($create_options['transactional']) && $create_options['transactional'] == '0')
        ? '0'
        : '1';
    $create_options['page_checksum'] = (isset($create_options['page_checksum'])) ? $create_options['page_checksum'] : '';
}

$pma_table = $GLOBALS['dbi']->getTable(
    $GLOBALS['db'],
    $GLOBALS['table']
);
$reread_info = false;
$table_alters = array();

$operations = new Operations();

/**
 * If the table has to be moved to some other database
 */
if (isset($_REQUEST['submit_move']) || isset($_REQUEST['submit_copy'])) {
    //$_message = '';
    $operations->moveOrCopyTable($db, $table);
    // This was ended in an Ajax call
    exit;
}
/**
 * If the table has to be maintained
 */
if (isset($_REQUEST['table_maintenance'])) {
    include_once 'sql.php';
    unset($result);
}
/**
 * Updates table comment, type and options if required
 */
if (isset($_REQUEST['submitoptions'])) {
    $_message = '';
    $warning_messages = array();

    if (isset($_REQUEST['new_name'])) {
        // Get original names before rename operation
        $oldTable = $pma_table->getName();
        $oldDb = $pma_table->getDbName();

        if ($pma_table->rename($_REQUEST['new_name'])) {
            if (isset($_REQUEST['adjust_privileges'])
                && ! empty($_REQUEST['adjust_privileges'])
            ) {
                $operations->adjustPrivilegesRenameOrMoveTable(
                    $oldDb, $oldTable, $_REQUEST['db'], $_REQUEST['new_name']
                );
            }

            // Reselect the original DB
            $GLOBALS['db'] = $oldDb;
            $GLOBALS['dbi']->selectDb($oldDb);
            $_message .= $pma_table->getLastMessage();
            $result = true;
            $GLOBALS['table'] = $pma_table->getName();
            $reread_info = true;
            $reload = true;
        } else {
            $_message .= $pma_table->getLastError();
            $result = false;
        }
    }

    if (! empty($_REQUEST['new_tbl_storage_engine'])
        && mb_strtoupper($_REQUEST['new_tbl_storage_engine']) !== $tbl_storage_engine
    ) {
        $new_tbl_storage_engine = mb_strtoupper($_REQUEST['new_tbl_storage_engine']);

        if ($pma_table->isEngine('ARIA')) {
            $create_options['transactional'] = (isset($create_options['transactional']) && $create_options['transactional'] == '0')
                ? '0'
                : '1';
            $create_options['page_checksum'] = (isset($create_options['page_checksum'])) ? $create_options['page_checksum'] : '';
        }
    } else {
        $new_tbl_storage_engine = '';
    }

    $row_format = (isset($create_options['row_format']))
        ? $create_options['row_format']
        : $pma_table->getRowFormat();

    $table_alters = $operations->getTableAltersArray(
        $pma_table,
        $create_options['pack_keys'],
        (empty($create_options['checksum']) ? '0' : '1'),
        ((isset($create_options['page_checksum'])) ? $create_options['page_checksum'] : ''),
        (empty($create_options['delay_key_write']) ? '0' : '1'),
        $row_format,
        $new_tbl_storage_engine,
        ((isset($create_options['transactional']) && $create_options['transactional'] == '0') ? '0' : '1'),
        $tbl_collation
    );

    if (count($table_alters) > 0) {
        $sql_query      = 'ALTER TABLE '
            . Util::backquote($GLOBALS['table']);
        $sql_query     .= "\r\n" . implode("\r\n", $table_alters);
        $sql_query     .= ';';
        $result        .= $GLOBALS['dbi']->query($sql_query) ? true : false;
        $reread_info    = true;
        unset($table_alters);
        $warning_messages = $operations->getWarningMessagesArray();
    }

    if (isset($_REQUEST['tbl_collation'])
        && ! empty($_REQUEST['tbl_collation'])
        && isset($_REQUEST['change_all_collations'])
        && ! empty($_REQUEST['change_all_collations'])
    ) {
        $operations->changeAllColumnsCollation(
            $GLOBALS['db'], $GLOBALS['table'], $_REQUEST['tbl_collation']
        );
    }
}
/**
 * Reordering the table has been requested by the user
 */
if (isset($_REQUEST['submitorderby']) && ! empty($_REQUEST['order_field'])) {
    list($sql_query, $result) = $operations->getQueryAndResultForReorderingTable();
} // end if

/**
 * A partition operation has been requested by the user
 */
if (isset($_REQUEST['submit_partition'])
    && ! empty($_REQUEST['partition_operation'])
) {
    list($sql_query, $result) = $operations->getQueryAndResultForPartition();
} // end if

if ($reread_info) {
    // to avoid showing the old value (for example the AUTO_INCREMENT) after
    // a change, clear the cache
    $GLOBALS['dbi']->clearTableCache();
    $GLOBALS['dbi']->selectDb($GLOBALS['db']);
    $GLOBALS['showtable'] = $pma_table->getStatusInfo(null, true);
    if ($pma_table->isView()) {
        $tbl_is_view = true;
        $tbl_storage_engine = __('View');
        $show_comment = null;
    } else {
        $tbl_is_view = false;
        $tbl_storage_engine = $pma_table->getStorageEngine();
        $show_comment = $pma_table->getComment();
    }
    $tbl_collation = $pma_table->getCollation();
    $table_info_num_rows = $pma_table->getNumRows();
    $row_format = $pma_table->getRowFormat();
    $auto_increment = $pma_table->getAutoIncrement();
    $create_options = $pma_table->getCreateOptions();
}
unset($reread_info);

if (isset($result) && empty($message_to_show)) {
    if (empty($_message)) {
        if (empty($sql_query)) {
            $_message = Message::success(__('No change'));
        } else {
            $_message = $result
                ? Message::success()
                : Message::error();
        }

        if ($response->isAjax()) {
            $response->setRequestStatus($_message->isSuccess());
            $response->addJSON('message', $_message);
            if (!empty($sql_query)) {
                $response->addJSON(
                    'sql_query', Util::getMessage(null, $sql_query)
                );
            }
            exit;
        }
    } else {
        $_message = $result
            ? Message::success($_message)
            : Message::error($_message);
    }

    if (! empty($warning_messages)) {
        $_message = new Message;
        $_message->addMessagesString($warning_messages);
        $_message->isError(true);
        if ($response->isAjax()) {
            $response->setRequestStatus(false);
            $response->addJSON('message', $_message);
            if (!empty($sql_query)) {
                $response->addJSON(
                    'sql_query', Util::getMessage(null, $sql_query)
                );
            }
            exit;
        }
        unset($warning_messages);
    }

    if (empty($sql_query)) {
        $response->addHTML(
            $_message->getDisplay()
        );
    } else {
        $response->addHTML(
            Util::getMessage($_message, $sql_query)
        );
    }
    unset($_message);
}

$url_params['goto']
    = $url_params['back']
        = 'tbl_operations.php';

/**
 * Get columns names
 */
$columns = $GLOBALS['dbi']->getColumns($GLOBALS['db'], $GLOBALS['table']);

/**
 * Displays the page
 */

/**
 * Order the table
 */
$hideOrderTable = false;
// `ALTER TABLE ORDER BY` does not make sense for InnoDB tables that contain
// a user-defined clustered index (PRIMARY KEY or NOT NULL UNIQUE index).
// InnoDB always orders table rows according to such an index if one is present.
if ($tbl_storage_engine == 'INNODB') {
    $indexes = Index::getFromTable($GLOBALS['table'], $GLOBALS['db']);
    foreach ($indexes as $name => $idx) {
        if ($name == 'PRIMARY') {
            $hideOrderTable = true;
            break;
        } elseif (! $idx->getNonUnique()) {
            $notNull = true;
            foreach ($idx->getColumns() as $column) {
                if ($column->getNull()) {
                    $notNull = false;
                    break;
                }
            }
            if ($notNull) {
                $hideOrderTable = true;
                break;
            }
        }
    }
}
if (! $hideOrderTable) {
    $response->addHTML($operations->getHtmlForOrderTheTable($columns));
}

/**
 * Move table
 */
$response->addHTML($operations->getHtmlForMoveTable());

if (mb_strstr($show_comment, '; InnoDB free') === false) {
    if (mb_strstr($show_comment, 'InnoDB free') === false) {
        // only user entered comment
        $comment = $show_comment;
    } else {
        // here we have just InnoDB generated part
        $comment = '';
    }
} else {
    // remove InnoDB comment from end, just the minimal part (*? is non greedy)
    $comment = preg_replace('@; InnoDB free:.*?$@', '', $show_comment);
}

// PACK_KEYS: MyISAM or ISAM
// DELAY_KEY_WRITE, CHECKSUM, : MyISAM only
// AUTO_INCREMENT: MyISAM and InnoDB since 5.0.3, PBXT

// Here should be version check for InnoDB, however it is supported
// in >5.0.4, >4.1.12 and >4.0.11, so I decided not to
// check for version

$response->addHTML(
    $operations->getTableOptionDiv(
        $pma_table, $comment, $tbl_collation, $tbl_storage_engine,
        $create_options['pack_keys'],
        $auto_increment,
        (empty($create_options['delay_key_write']) ? '0' : '1'),
        ((isset($create_options['transactional']) && $create_options['transactional'] == '0') ? '0' : '1'),
        ((isset($create_options['page_checksum'])) ? $create_options['page_checksum'] : ''),
        (empty($create_options['checksum']) ? '0' : '1')
    )
);

/**
 * Copy table
 */
$response->addHTML($operations->getHtmlForCopytable());

/**
 * Table maintenance
 */
$response->addHTML(
    $operations->getHtmlForTableMaintenance($pma_table, $url_params)
);

if (! (isset($db_is_system_schema) && $db_is_system_schema)) {
    $truncate_table_url_params = array();
    $drop_table_url_params = array();

    if (! $tbl_is_view
        && ! (isset($db_is_system_schema) && $db_is_system_schema)
    ) {
        $this_sql_query = 'TRUNCATE TABLE '
            . Util::backquote($GLOBALS['table']);
        $truncate_table_url_params = array_merge(
            $url_params,
            array(
                'sql_query' => $this_sql_query,
                'goto' => 'tbl_structure.php',
                'reload' => '1',
                'message_to_show' => sprintf(
                    __('Table %s has been emptied.'),
                    htmlspecialchars($table)
                ),
            )
        );
    }
    if (! (isset($db_is_system_schema) && $db_is_system_schema)) {
        $this_sql_query = 'DROP TABLE '
            . Util::backquote($GLOBALS['table']);
        $drop_table_url_params = array_merge(
            $url_params,
            array(
                'sql_query' => $this_sql_query,
                'goto' => 'db_operations.php',
                'reload' => '1',
                'purge' => '1',
                'message_to_show' => sprintf(
                    ($tbl_is_view
                        ? __('View %s has been dropped.')
                        : __('Table %s has been dropped.')
                    ),
                    htmlspecialchars($table)
                ),
                // table name is needed to avoid running
                // PhpMyAdmin\RelationCleanup::database() on the whole db later
                'table' => $GLOBALS['table'],
            )
        );
    }
    $response->addHTML(
        $operations->getHtmlForDeleteDataOrTable(
            $truncate_table_url_params,
            $drop_table_url_params
        )
    );
}

if (Partition::havePartitioning()) {
    $partition_names = Partition::getPartitionNames($db, $table);
    // show the Partition maintenance section only if we detect a partition
    if (! is_null($partition_names[0])) {
        $response->addHTML(
            $operations->getHtmlForPartitionMaintenance($partition_names, $url_params)
        );
    } // end if
} // end if
unset($partition_names);

// Referential integrity check
// The Referential integrity check was intended for the non-InnoDB
// tables for which the relations are defined in pmadb
// so I assume that if the current table is InnoDB, I don't display
// this choice (InnoDB maintains integrity by itself)

if ($cfgRelation['relwork'] && ! $pma_table->isEngine("INNODB")) {
    $GLOBALS['dbi']->selectDb($GLOBALS['db']);
    $foreign = $relation->getForeigners($GLOBALS['db'], $GLOBALS['table'], '', 'internal');

    if (! empty($foreign)) {
        $response->addHTML(
            $operations->getHtmlForReferentialIntegrityCheck($foreign, $url_params)
        );
    } // end if ($foreign)

} // end  if (!empty($cfg['Server']['relation']))

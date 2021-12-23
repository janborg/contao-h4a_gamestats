<?php

use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;

/**
 * Add backend table to calendar
 */
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_h4a_playerscores';
$GLOBALS['BE_MOD']['content']['calendar']['lookup_scores'] = ['Janborg\H4aGamestats\Backend\LookupScoresController', 'lookupScores'];

/**
 * models
 */
$GLOBALS['TL_MODELS']['tl_h4a_playerscores'] = H4aPlayerscoresModel::class;
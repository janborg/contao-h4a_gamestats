<?php

use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aGamestats\Model\H4aTimelineModel;

/**
 * Add backend table to calendar
 */
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_h4a_playerscores';
$GLOBALS['BE_MOD']['content']['calendar']['tables'][] = 'tl_h4a_timeline';
$GLOBALS['BE_MOD']['content']['calendar']['lookup_scores'] = ['Janborg\H4aGamestats\Backend\LookupScoresController', 'lookupScores'];
$GLOBALS['BE_MOD']['content']['calendar']['lookup_timeline'] = ['Janborg\H4aGamestats\Backend\LookupTimelineController', 'lookupTimeline'];


/**
 * models
 */
$GLOBALS['TL_MODELS']['tl_h4a_playerscores'] = H4aPlayerscoresModel::class;
$GLOBALS['TL_MODELS']['tl_h4a_timeline'] = H4aTimelineModel::class;
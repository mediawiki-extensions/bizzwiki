<?php

/*
 * Created on Sep 7, 2006
 *
 * API for MediaWiki 1.8+
 *
 * Copyright (C) 2006 Yuri Astrakhan <Firstname><Lastname>@gmail.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if (!defined('MEDIAWIKI')) {
	// Eclipse helper - will be ignored in production
	require_once ('ApiQueryBase.php');
}

/**
 * A query action to enumerate revisions of a given page, or show top revisions of multiple pages.
 * Various pieces of information may be shown - flags, comments, and the actual wiki markup of the rev. 
 * In the enumeration mode, ranges of revisions may be requested and filtered. 
 * 
 * @addtogroup API
 */
class ApiQueryRevisions extends ApiQueryBase {

	public function __construct($query, $moduleName) {
		parent :: __construct($query, $moduleName, 'rv');
	}

	private $fld_ids = false, $fld_flags = false, $fld_timestamp = false, 
			$fld_comment = false, $fld_user = false, $fld_content = false;

	public function execute() {
		$limit = $startid = $endid = $start = $end = $dir = $prop = $user = $excludeuser = null;
		extract($this->extractRequestParams());

		// If any of those parameters are used, work in 'enumeration' mode.
		// Enum mode can only be used when exactly one page is provided.
		// Enumerating revisions on multiple pages make it extremelly 
		// difficult to manage continuations and require additional sql indexes  
		$enumRevMode = (!is_null($user) || !is_null($excludeuser) || !is_null($limit) || !is_null($startid) || !is_null($endid) || $dir === 'newer' || !is_null($start) || !is_null($end));

		$pageSet = $this->getPageSet();
		$pageCount = $pageSet->getGoodTitleCount();
		$revCount = $pageSet->getRevisionCount();

		// Optimization -- nothing to do
		if ($revCount === 0 && $pageCount === 0)
			return;

		if ($revCount > 0 && $enumRevMode)
			$this->dieUsage('The revids= parameter may not be used with the list options (limit, startid, endid, dirNewer, start, end).', 'revids');

		if ($pageCount > 1 && $enumRevMode)
			$this->dieUsage('titles, pageids or a generator was used to supply multiple pages, but the limit, startid, endid, dirNewer, user, excludeuser, start, and end parameters may only be used on a single page.', 'multpages');

		$this->addTables('revision');
		$this->addWhere('rev_deleted=0');

		$prop = array_flip($prop);

		// These field are needed regardless of the client requesting them
		$this->addFields('rev_id');
		$this->addFields('rev_page');

		// Optional fields
		$this->fld_ids = isset ($prop['ids']);
		// $this->addFieldsIf('rev_text_id', $this->fld_ids); // should this be exposed?
		$this->fld_flags = $this->addFieldsIf('rev_minor_edit', isset ($prop['flags']));
		$this->fld_timestamp = $this->addFieldsIf('rev_timestamp', isset ($prop['timestamp']));
		$this->fld_comment = $this->addFieldsIf('rev_comment', isset ($prop['comment']));

		if (isset ($prop['user'])) {
			$this->addFields('rev_user');
			$this->addFields('rev_user_text');
			$this->fld_user = true;
		}
		if (isset ($prop['content'])) {

			// For each page we will request, the user must have read rights for that page
			foreach ($pageSet->getGoodTitles() as $title) {
				if( !$title->userCanRead() )
					$this->dieUsage(
						'The current user is not allowed to read ' . $title->getPrefixedText(),
						'accessdenied');
			}

			$this->addTables('text');
			$this->addWhere('rev_text_id=old_id');
			$this->addFields('old_id');
			$this->addFields('old_text');
			$this->addFields('old_flags');
			$this->fld_content = true;
		}

		$userMax = ($this->fld_content ? 50 : 500);
		$botMax = ($this->fld_content ? 200 : 10000);

		if ($enumRevMode) {

			// This is mostly to prevent parameter errors (and optimize sql?)
			if (!is_null($startid) && !is_null($start))
				$this->dieUsage('start and startid cannot be used together', 'badparams');

			if (!is_null($endid) && !is_null($end))
				$this->dieUsage('end and endid cannot be used together', 'badparams');

			if(!is_null($user) && !is_null( $excludeuser))
				$this->dieUsage('user and excludeuser cannot be used together', 'badparams');			
			
			// This code makes an assumption that sorting by rev_id and rev_timestamp produces
			// the same result. This way users may request revisions starting at a given time,
			// but to page through results use the rev_id returned after each page.
			// Switching to rev_id removes the potential problem of having more than 
			// one row with the same timestamp for the same page. 
			// The order needs to be the same as start parameter to avoid SQL filesort.

			if (is_null($startid))
				$this->addWhereRange('rev_timestamp', $dir, $start, $end);
			else
				$this->addWhereRange('rev_id', $dir, $startid, $endid);

			// must manually initialize unset limit
			if (is_null($limit))
				$limit = 10;
			$this->validateLimit('limit', $limit, 1, $userMax, $botMax);

			// There is only one ID, use it
			$this->addWhereFld('rev_page', current(array_keys($pageSet->getGoodTitles())));
			
			if(!is_null($user)) {
				$this->addWhereFld('rev_user_text', $user);
			} elseif (!is_null( $excludeuser)) {
				$this->addWhere('rev_user_text != ' . $this->getDB()->addQuotes($excludeuser));
			}
		}
		elseif ($revCount > 0) {
			$this->validateLimit('rev_count', $revCount, 1, $userMax, $botMax);

			// Get all revision IDs
			$this->addWhereFld('rev_id', array_keys($pageSet->getRevisionIDs()));

			// assumption testing -- we should never get more then $revCount rows.
			$limit = $revCount;
		}
		elseif ($pageCount > 0) {
			// When working in multi-page non-enumeration mode,
			// limit to the latest revision only
			$this->addTables('page');
			$this->addWhere('page_id=rev_page');
			$this->addWhere('page_latest=rev_id');
			$this->validateLimit('page_count', $pageCount, 1, $userMax, $botMax);

			// Get all page IDs
			$this->addWhereFld('page_id', array_keys($pageSet->getGoodTitles()));

			// assumption testing -- we should never get more then $pageCount rows.
			$limit = $pageCount;
		} else
			ApiBase :: dieDebug(__METHOD__, 'param validation?');

		$this->addOption('LIMIT', $limit +1);

		$data = array ();
		$count = 0;
		$res = $this->select(__METHOD__);

		$db = $this->getDB();
		while ($row = $db->fetchObject($res)) {

			if (++ $count > $limit) {
				// We've reached the one extra which shows that there are additional pages to be had. Stop here...
				if (!$enumRevMode)
					ApiBase :: dieDebug(__METHOD__, 'Got more rows then expected'); // bug report
				$this->setContinueEnumParameter('startid', intval($row->rev_id));
				break;
			}

			$this->getResult()->addValue(
				array (
					'query',
					'pages',
					intval($row->rev_page),
					'revisions'),
				null,
				$this->extractRowInfo($row));
		}
		$db->freeResult($res);

		// Ensure that all revisions are shown as '<rev>' elements
		$result = $this->getResult();
		if ($result->getIsRawMode()) {
			$data =& $result->getData();
			foreach ($data['query']['pages'] as & $page) {
				if (is_array($page) && array_key_exists('revisions', $page)) {
					$result->setIndexedTagName($page['revisions'], 'rev');
				}
			}
		}
	}

	private function extractRowInfo($row) {

		$vals = array ();

		if ($this->fld_ids) {
			$vals['revid'] = intval($row->rev_id);
			// $vals['oldid'] = intval($row->rev_text_id);	// todo: should this be exposed?
		}
		
		if ($this->fld_flags && $row->rev_minor_edit)
			$vals['minor'] = '';

		if ($this->fld_user) {
			$vals['user'] = $row->rev_user_text;
			if (!$row->rev_user)
				$vals['anon'] = '';
		}

		if ($this->fld_timestamp) {
			$vals['timestamp'] = wfTimestamp(TS_ISO_8601, $row->rev_timestamp);
		}
		
		if ($this->fld_comment && !empty ($row->rev_comment)) {
			$vals['comment'] = $row->rev_comment;
		}
		
		if ($this->fld_content) {
			ApiResult :: setContent($vals, Revision :: getRevisionText($row));
		}
		
		return $vals;
	}

	protected function getAllowedParams() {
		return array (
			'prop' => array (
				ApiBase :: PARAM_ISMULTI => true,
				ApiBase :: PARAM_DFLT => 'ids|timestamp|flags|comment|user',
				ApiBase :: PARAM_TYPE => array (
					'ids',
					'flags',
					'timestamp',
					'user',
					'comment',
					'content'
				)
			),
			'limit' => array (
				ApiBase :: PARAM_TYPE => 'limit',
				ApiBase :: PARAM_MIN => 1,
				ApiBase :: PARAM_MAX => ApiBase :: LIMIT_SML1,
				ApiBase :: PARAM_MAX2 => ApiBase :: LIMIT_SML2
			),
			'startid' => array (
				ApiBase :: PARAM_TYPE => 'integer'
			),
			'endid' => array (
				ApiBase :: PARAM_TYPE => 'integer'
			),
			'start' => array (
				ApiBase :: PARAM_TYPE => 'timestamp'
			),
			'end' => array (
				ApiBase :: PARAM_TYPE => 'timestamp'
			),
			'dir' => array (
				ApiBase :: PARAM_DFLT => 'older',
				ApiBase :: PARAM_TYPE => array (
					'newer',
					'older'
				)
			),
			'user' => array(
				ApiBase :: PARAM_TYPE => 'user'
			),
			'excludeuser' => array(
				ApiBase :: PARAM_TYPE => 'user'
			)
		);
	}

	protected function getParamDescription() {
		return array (
			'prop' => 'Which properties to get for each revision.',
			'limit' => 'limit how many revisions will be returned (enum)',
			'startid' => 'from which revision id to start enumeration (enum)',
			'endid' => 'stop revision enumeration on this revid (enum)',
			'start' => 'from which revision timestamp to start enumeration (enum)',
			'end' => 'enumerate up to this timestamp (enum)',
			'dir' => 'direction of enumeration - towards "newer" or "older" revisions (enum)',
			'user' => 'only include revisions made by user',
			'excludeuser' => 'exclude revisions made by user',
		);
	}

	protected function getDescription() {
		return array (
			'Get revision information.',
			'This module may be used in several ways:',
			' 1) Get data about a set of pages (last revision), by setting titles or pageids parameter.',
			' 2) Get revisions for one given page, by using titles/pageids with start/end/limit params.',
			' 3) Get data about a set of revisions by setting their IDs with revids parameter.',
			'All parameters marked as (enum) may only be used with a single page (#2).'
		);
	}

	protected function getExamples() {
		return array (
			'Get data with content for the last revision of titles "API" and "Main Page":',
			'  api.php?action=query&prop=revisions&titles=API|Main%20Page&rvprop=timestamp|user|comment|content',
			'Get last 5 revisions of the "Main Page":',
			'  api.php?action=query&prop=revisions&titles=Main%20Page&rvlimit=5&rvprop=timestamp|user|comment',
			'Get first 5 revisions of the "Main Page":',
			'  api.php?action=query&prop=revisions&titles=Main%20Page&rvlimit=5&rvprop=timestamp|user|comment&rvdir=newer',
			'Get first 5 revisions of the "Main Page" made after 2006-05-01:',
			'  api.php?action=query&prop=revisions&titles=Main%20Page&rvlimit=5&rvprop=timestamp|user|comment&rvdir=newer&rvstart=20060501000000',
			'Get first 5 revisions of the "Main Page" that were not made made by anonymous user "127.0.0.1"',
			'  api.php?action=query&prop=revisions&titles=Main%20Page&rvlimit=5&rvprop=timestamp|user|comment&rvexcludeuser=127.0.0.1',
			'Get first 5 revisions of the "Main Page" that were made by the user "MediaWiki default"',
			'  api.php?action=query&prop=revisions&titles=Main%20Page&rvlimit=5&rvprop=timestamp|user|comment&rvuser=MediaWiki%20default',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: ApiQueryRevisions.php 24453 2007-07-30 08:09:15Z yurik $';
	}
}


<?php
if (!defined('BLARG')) die();

$rev = isset($_GET['rev']) ? (int)$_GET['rev'] : 0;
$page = getWikiPage($pageParams['page'], $rev);
$rev = min($rev, $page['revision']);

$urltitle = $page['id'];
$wikititle = htmlspecialchars(url2title($page['id']));
$title = 'Wiki &raquo; '.$wikititle;

if ($page['istalk']) 
	$links .= actionLinkTagItem('Page', 'wiki', substr($urltitle,5)).'<li>Discuss</li>';
else
	$links .= '<li>Page</li>'.actionLinkTagItem('Discuss', 'wiki', 'Talk:'.$urltitle);

if ($page['canedit'])
	$links .= actionLinkTagItem('Edit', 'wikiedit', $urltitle);

if ($page['ismain'])
	MakeCrumbs(array(actionLink('wiki') => 'Wiki'), $links);
else
	MakeCrumbs(array(actionLink('wiki') => 'Wiki', actionLink('wiki', $urltitle) => $wikititle), $links);
	
if ($page['flags'] & WIKI_PFLAG_DELETED) {
	$wikicontents = "This page has been deleted.";
} else if ($page['new']) {
	$wikicontents = "This page does not exist.";
	
	if ($page['canedit'])
		$wikicontents .= "<br><br>".actionLinkTag('Create it now', 'wikiedit', $urltitle);
} else {
	$revInfo = '';
	$revList = '';
	
	if ($rev > 0)  {
		$revs = Query("SELECT pt.revision r FROM {wiki_pages_text} pt WHERE pt.id={0} ORDER BY r ASC", $urltitle);
		while ($therev = Fetch($revs)) {
			if ($therev['r'] == $rev)
				$revList .= '&nbsp;'.$therev['r'].'&nbsp;';
			else
				$revList .= '&nbsp;'.actionLinkTag($therev['r'], 'wiki', $urltitle, 'rev='.$therev['r']).'&nbsp;';
		}
		
		$user = Fetch(Query("SELECT u.(_userfields) FROM {users} u WHERE u.id={0}", $page['user']));
		$user = getDataPrefix($user, 'u_');
		$revInfo = 'Viewing revision '.$rev.' (by '.userLink($user).' on '.formatdate($page['date']).')<br>(revisions: '.$revList.')<br><br>';
	}
	
	$wikicontents = $revInfo.wikiFilter($page['text'], $page['flags'] & WIKI_PFLAG_NOCONTBOX);
}

RenderTemplate('wiki', array(
	'wikititle' => $wikititle,
	'wikicontents' => $wikicontents,
));

?>

<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2022 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1.0
 */

/**
 * The top part of the outer layer of the boardindex
 */
function template_boardindex_outer_above()
{
	template_newsfader();
}

/**
 * This shows the newsfader
 */
function template_newsfader()
{
	global $context, $settings, $txt;

	// Show the news fader?  (assuming there are things to show...)
	if (!empty($settings['show_newsfader']) && !empty($context['news_lines']))
	{
		echo '
		<ul id="smf_slider" class="roundframe">';

		foreach ($context['news_lines'] as $news)
			echo '
			<li>', $news, '</li>';

		echo '
		</ul>
		<script>
			jQuery("#smf_slider").slippry({
				pause: ', $settings['newsfader_time'], ',
				adaptiveHeight: 0,
				captions: 0,
				controls: 0,
			});
		</script>';
	}
}

/**
 * This actually displays the board index
 */
function template_main()
{
	global $context, $txt, $scripturl;

	echo '
	<div id="boardindex_table" class="boardindex_table">';

	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	foreach ($context['categories'] as $category)
	{
		// If theres no parent boards we can see, avoid showing an empty category (unless its collapsed)
		if (empty($category['boards']) && !$category['is_collapsed'])
			continue;

		echo '
		<div class="main_container">
			<div class="bord cat_bar ', $category['is_collapsed'] ? 'collapsed' : '', '" id="category_', $category['id'], '">
				<h3 class="catbg">';

		// If this category even can collapse, show a link to collapse it.
		if ($category['can_collapse'])
			echo '
					<span id="category_', $category['id'], '_upshrink" class="', $category['is_collapsed'] ? 'toggle_down' : 'toggle_up', ' floatright" data-collapsed="', (int) $category['is_collapsed'], '" title="', !$category['is_collapsed'] ? $txt['hide_category'] : $txt['show_category'], '" style="display: none;"></span>';

		echo '
		<span class="block-header--icon"></span>', $category['link'], '
				</h3>', !empty($category['description']) ? '
				<div class="desc">' . $category['description'] . '</div>' : '', '
			</div>
			<div id="category_', $category['id'], '_boards" class="block-body block-body--collapsible is-active" ', (!empty($category['css_class']) ? ('class="' . $category['css_class'] . '"') : ''), $category['is_collapsed'] ? ' style="display: none;"' : '', '>';

		/* Each board in each category's boards has:
		new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
		children (see below.), link_children (easier to use.), children_new (are they new?),
		topics (# of), posts (# of), link, href, and last_post. (see below.) */
		foreach ($category['boards'] as $board)
		{
			echo '
				<div id="board_', $board['id'], '" class="node-body up_contain ', (!empty($board['css_class']) ? $board['css_class'] : ''), '">
					<div class="board_icon">
						', function_exists('template_bi_' . $board['type'] . '_icon') ? call_user_func('template_bi_' . $board['type'] . '_icon', $board) : template_bi_board_icon($board), '
					</div>
					<div class="info">
						', function_exists('template_bi_' . $board['type'] . '_info') ? call_user_func('template_bi_' . $board['type'] . '_info', $board) : template_bi_board_info($board), '
						';

			// Won't somebody think of the children!
			if (function_exists('template_bi_' . $board['type'] . '_children'))
				call_user_func('template_bi_' . $board['type'] . '_children', $board);
			else
				template_bi_board_children($board);

			echo '
					</div><!-- .info -->';

			// Show some basic information about the number of posts, etc.
			echo '
					<div class="board_stats">
						', function_exists('template_bi_' . $board['type'] . '_stats') ? call_user_func('template_bi_' . $board['type'] . '_stats', $board) : template_bi_board_stats($board), '
					</div>';

			// Show the last post if there is one.
			echo'
					<div class="lastpost">
						', function_exists('template_bi_' . $board['type'] . '_lastpost') ? call_user_func('template_bi_' . $board['type'] . '_lastpost', $board) : template_bi_board_lastpost($board), '
					</div>
				</div><!-- #board_[id] -->';
		}

		echo '
			</div><!-- #category_[id]_boards -->
		</div><!-- .main_container -->';
	}

	echo '
	</div><!-- #boardindex_table -->';

	// Show the mark all as read button?
	if ($context['user']['is_logged'] && !empty($context['categories']))
		echo '
	<div class="mark_read">
		', template_button_strip($context['mark_read_button'], 'right'), '
	</div>';
}

/**
 * Outputs the board icon for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_icon($board)
{
	global $context, $scripturl;

	$icon_type = $board['new'] ? 'fas' : 'far';

	echo '
	<span class="nodeIcon hasGlyph new"><a href="', ($context['user']['is_guest'] ? $board['href'] : $scripturl . '?action=unread;board=' . $board['id'] . '.0;children'), '">
			', icon("$icon_type fa-comments"), '
		</a></span>
		<span class="forum-mini-status board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '>
		</span>';
}

/**
 * Outputs the board icon for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_icon($board)
{
	global $context, $scripturl;

	echo '
		<a href="', $board['href'], '" class="board_', $board['board_class'], '"', !empty($board['board_tooltip']) ? ' title="' . $board['board_tooltip'] . '"' : '', '>
			', icon('fas fa-external-link-alt'), '
		</a>';
}

/**
 * Outputs the board info for a standard board or redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_board_info($board)
{
	global $context, $scripturl, $txt;

	echo '
		<a class="subject mobile_subject" href="', $board['href'], '" id="b', $board['id'], '">
			', $board['name'], '
		</a>';

	// Has it outstanding posts for approval?
	if ($board['can_approve_posts'] && ($board['unapproved_posts'] || $board['unapproved_topics']))
		echo '
		<a href="', $scripturl, '?action=moderate;area=postmod;sa=', ($board['unapproved_topics'] > 0 ? 'topics' : 'posts'), ';brd=', $board['id'], ';', $context['session_var'], '=', $context['session_id'], '" title="', sprintf($txt['unapproved_posts'], $board['unapproved_topics'], $board['unapproved_posts']), '" class="moderation_link amt">!</a>';

	echo '
		<div class="board_description">', $board['description'], '</div>';

	// Show the "Moderators: ". Each has name, href, link, and id. (but we're gonna use link_moderators.)
	if (!empty($board['link_moderators']))
		echo '
		<p class="moderators">', count($board['link_moderators']) == 1 ? $txt['moderator'] : $txt['moderators'], ': ', implode(', ', $board['link_moderators']), '</p>';
}

/**
 * Outputs the board stats for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_stats($board)
{
	global $txt;

	echo '
		<div class="counter_stats">
		<span title="', $txt['posts'], '"><span><i class="icon fas fa-reply-all"></i></span>
		<span class="stats_no">', comma_format($board['posts']), '</span> 
		</span>
		</div>
		<div class="counter_stats">
		<span title="', $txt['board_topics'], '">
		<span><i class="fa-solid fa-eye"></i></span>
		<span class="stats_no">', comma_format($board['topics']), '</span>
		</span> 
		</div>';
}

/**
 * Outputs the board stats for a redirect.
 *
 * @param array $board Current board information.
 */
function template_bi_redirect_stats($board)
{
	global $txt;

	echo '
		<p>', icon('fas fa-external-link-alt'), ' ', comma_format($board['posts']), '</p>';
}

/**
 * Outputs the board lastposts for a standard board or a redirect.
 * When on a mobile device, this may be hidden if no last post exists.
 *
 * @param array $board Current board information.
 */
function template_bi_board_lastpost($board)
{
	if (empty($board['last_post']['id']))
		return;

	echo '
		<div class="topic-item">
			<div class="topic-item-poster-avatar">', $board['last_post']['member']['avatar']['image'], '</div>
			<div class="topic-item-content">
				<div class="topic-item-title">', $board['last_post']['link'], '</div>
				<div class="topic-item-details">
					<div class="topic-item-poster">', $board['last_post']['member']['link'], '</div>
					<div class="topic-item-time">', icon('far fa-clock'), ' ', timeformat($board['last_post']['timestamp']), '</div>
				</div>
			</div>
		</div>';
}

/**
 * Outputs the board children for a standard board.
 *
 * @param array $board Current board information.
 */
function template_bi_board_children($board)
{
	global $txt, $scripturl, $context;

	// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
	if (!empty($board['children']))
	{
		// Sort the links into an array with new boards bold so it can be imploded.
		$children = array();
		/* Each child in each board's children has:
			id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
		foreach ($board['children'] as $child)
		{
			if (!$child['is_redirect'])
				$child['link'] = '' . ($child['new'] ? '<a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')" class="new_posts">' . $txt['new'] . '</a> ' : '') . '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="board_new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . '</a>';
			else
				$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . ' - ' . $child['short_description'] . '">' . $child['name'] . '</a>';

			// Has it posts awaiting approval?
			if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
				$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link amt">!</a>';

			$children[] = $child['new'] ? '' . $child['link'] . '' : '' . $child['link'] . '';
		}

		echo '
<script>
 function myFunction_', $board['id'], '(){
	 document.getElementById("myDropdown_', $board['id'], '").classList.toggle("show");
	 }	  
               window.onclick = function(event) {
				if (!event.target.matches(\'.dropbtn\')) {
				var dropdowns = document.getElementsByClassName("dropdown-content");
               var i;
			   for (i = 0; i < dropdowns.length; i++) {
			   var openDropdown = dropdowns[i];
               if (openDropdown.classList.contains(\'show\')) {
			  openDropdown.classList.remove(\'show\');
			  }
			 }
			}
		 }
			  </script>
                  <div id="board_', $board['id'], '_children" class="dropdown_children">
					<a onclick="myFunction_', $board['id'], '()" class="dropbtn_children">', $txt['sub_boards'], ' </a> 
					 <div id="myDropdown_', $board['id'], '" class="dropdown-content_children"> ', implode($children), '</div>  
					  </div>';		
	}
}

/**
 * The lower part of the outer layer of the board index
 */
function template_boardindex_outer_below()
{
	template_info_center();
}

/**
 * Displays the info center
 */
function template_info_center()
{
	global $context, $options, $txt;

	if (empty($context['info_center']))
		return;

	// Here's where the "Info Center" starts...
	echo '
	<div id="info_center">';

	foreach ($context['info_center'] as $block)
	{
		$func = 'template_ic_block_' . $block['tpl'];
		$func();
	}

	echo '
	</div><!-- #info_center -->';
}

/**
 * The recent posts section of the info center
 */
function template_ic_block_recent()
{
	global $context, $scripturl, $settings, $txt;

	// This is the "Recent Posts" bar.
	echo '
	<div class="xgt-ForumIstatistik-Govde">
		<div class="block istatistik-blogu">		
			<div class="KonuHucre-Genis">
				<h4 class="istatikKonu-TabHeader block-tabHeader tabs hScroller">
				<span class="floatleft hScroller-scroll is-calculated" style="margin-bottom: -45px;">
					<a class="tabs-tab is-active" href="', $scripturl, '?action=recent"><span class="main_icons recent_posts"></span> ', $txt['recent_posts'], '</a>
				</span>
				<span class="floatright stabview-refresh">
				<a href="'.$scripturl.'"><i class="fas fa-sync-alt"></i></a>
				  </span>
				</h4>
			<div id="recent_posts_content">';

	// Only show one post.
	if ($settings['number_recent_posts'] == 1)
	{
		// latest_post has link, href, time, subject, short_subject (shortened with...), and topic. (its id.)
		echo '
				<p id="infocenter_onepost" class="inline">
					<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a> ', sprintf($txt['is_recent_updated'], '&quot;' . $context['latest_post']['link'] . '&quot;'), ' (', $context['latest_post']['time'], ')<br>
				</p>';
	}
	// Show lots of posts.
	elseif (!empty($context['latest_posts']))
	{
		echo '
		           <ul class="tabPanes">
					<li class="is-active">
					<div class="MiniHeader">
					<div class="MiniHeaderHucre IstatistikAvatar">#</div>
						<div class="IstatistikHucre">', $txt['message'], '</div>
						<div class="MiniHeaderHucre IstatistikCevap">', $txt['board'], '</div>
						<div class="MiniHeaderHucre IstatistikForum">', $txt['author'], '</div>
						<div class="MiniHeaderHucre IstatistikSonCevap">', $txt['date'], '</div>
					</div>
					</li>';

		/* Each post in latest_posts has:
			board (with an id, name, and link.), topic (the topic's id.), poster (with id, name, and link.),
			subject, short_subject (shortened with...), time, link, and href. */
			global $memberContext;	
			foreach ($context['latest_posts'] as $post)
			{
			$post['poster']['id']=!empty($post['poster']['id']==0)? 1 :$post['poster']['id'] ;
			loadMemberData($post['poster']['id']);
			loadMemberContext($post['poster']['id']);
				echo '
				<ul class="xgtIstatistikListe">
				<li class="xgtIstatistikVerileri">
				<div class="IstatistikHucre IstatistikSirasi"></div>';
						if($memberContext[$post['poster']['id']]['avatar']['image'])
						echo'
						<div class="IstatistikHucre IstatistikAvatar"><span class="avatar avatar--s"> 
					', $memberContext[$post['poster']['id']]['avatar']['image'],'</span></div>
						<div class="IstatistikHucre KonuBaglantisi  OkunmamisVeri" data-author="ravva">', $post['link'], '</div>
						<div class="IstatistikHucre konuIkonlari">
						<i class="icon far fa-comments"></i>
                          </div>
						<div class="IstatistikHucre IstatistikCevap">', $post['board']['link'], '</div>
						<div class="IstatistikHucre IstatistikForum">', $post['poster']['link'], '</div>
						<div class="IstatistikHucre IstatistikSonCevap">', $post['time'], '</div>
						</li>
					</ul>';
			}
		echo '
				</ul>';
	}
	echo '
			</div>
			<div class="xgtForumIstatistik-Footer"></div>
		</div>
	</div>
</div><!-- #recent_posts_content -->';
}

/**
 * The calendar section of the info center
 */
function template_ic_block_calendar()
{
	global $context, $scripturl, $txt;

	// Show information about events, birthdays, and holidays on the calendar.
	echo '
			<div class="sub_bar">
				<h4 class="subbg">
					<a href="', $scripturl, '?action=calendar' . '"><span class="main_icons calendar"></span> ', $context['calendar_only_today'] ? $txt['calendar_today'] : $txt['calendar_upcoming'], '</a>
				</h4>
			</div>';

	// Holidays like "Christmas", "Chanukah", and "We Love [Unknown] Day" :P
	if (!empty($context['calendar_holidays']))
		echo '
			<p class="inline holiday">
				<span>', $txt['calendar_prompt'], '</span> ', implode(', ', $context['calendar_holidays']), '
			</p>';

	// People's birthdays. Like mine. And yours, I guess. Kidding.
	if (!empty($context['calendar_birthdays']))
	{
		echo '
			<p class="inline">
				<span class="birthday">', $context['calendar_only_today'] ? $txt['birthdays'] : $txt['birthdays_upcoming'], '</span>';

		// Each member in calendar_birthdays has: id, name (person), age (if they have one set?), is_last. (last in list?), and is_today (birthday is today?)
		foreach ($context['calendar_birthdays'] as $member)
			echo '
				<a href="', $scripturl, '?action=profile;u=', $member['id'], '">', $member['is_today'] ? '<strong class="fix_rtl_names">' : '', $member['name'], $member['is_today'] ? '</strong>' : '', isset($member['age']) ? ' (' . $member['age'] . ')' : '', '</a>', $member['is_last'] ? '' : ', ';

		echo '
			</p>';
	}

	// Events like community get-togethers.
	if (!empty($context['calendar_events']))
	{
		echo '
			<p class="inline">
				<span class="event">', $context['calendar_only_today'] ? $txt['events'] : $txt['events_upcoming'], '</span> ';

		// Each event in calendar_events should have:
		//		title, href, is_last, can_edit (are they allowed?), modify_href, and is_today.
		foreach ($context['calendar_events'] as $event)
			echo '
				', $event['can_edit'] ? '<a href="' . $event['modify_href'] . '" title="' . $txt['calendar_edit'] . '"><span class="main_icons calendar_modify"></span></a> ' : '', $event['href'] == '' ? '' : '<a href="' . $event['href'] . '">', $event['is_today'] ? '<strong>' . $event['title'] . '</strong>' : $event['title'], $event['href'] == '' ? '' : '</a>', $event['is_last'] ? '<br>' : ', ';
		echo '
			</p>';
	}
}

/**
 * The stats section of the info center
 */
function template_ic_block_stats()
{
	global $context, $scripturl, $txt, $modSettings, $settings;

	// Show statistical style information...
	echo '
	<ul class="forum-static">
			<li class="left">
			<span class="index_stats_item online">
			<span class="count">
			 <span><i class="fa-brands fa-angellist"></i></span>
			 <span>', $txt['most_online_ever'], '<br> <b>', comma_format($modSettings['mostOnline']), '</b></span>
			 </span>
			 <span class="link"> <a href="', $scripturl, '?action=stats" title="', $txt['more_stats'], '" class="button-slide"><span class="btn-text">', $txt['forum_stats'], '</span>
			 <span class="btn-icon"><i class="fa-solid fa-angle-right"></i></span></a></span>
			</span>
			<span class="vb">
			<span class="threads">
			<span class="bg-tagerine-gradient"><i class="fa-regular fa-user"></i></span>
			 <span>', $txt['uyeler'], '<br> <b>', $context['common_stats']['total_members'], '</b></span>
			 </span>
			<span class="posts">
			 <span class="bg-amethyst-gradient"><i class="fa-solid fa-microphone-lines"></i></span>
			  <span>', $txt['konular'], '<br> <b>', $context['common_stats']['total_topics'], '</b></span>
			  </span>
			  <span class="users">
			  <span class="bg-picton-gradient"><i class="fa-regular fa-comments"></i></span>
			   <span>', $txt['mesajlar'], '<br> <b>', $context['common_stats']['total_posts'], ' </b></span>
			   </span></span>
			</li>';
	echo '
			<li class="right">
	  <span class="bg-sun-gradient"><i class="fa-solid fa-user-pen"></i></span> 
	  <span>', $txt['son_kayit'], '<br>' . $context['common_stats']['latest_member']['link'] . '</span>
				</li>
			</ul>';
}

/**
 * The who's online section of the info center
 */
function template_ic_block_online()
{
	global $context, $scripturl, $txt, $modSettings, $settings;
	// "Users online" - in order of activity.
	echo '
	<div class="users-online">
	<div class="index_stats_item online1">
				<span class="count">
					', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<span><i class="fa-solid fa-users"></i></span>', $context['show_who'] ? '</a>' : '', '
				</span>
			<span class="ml-8">
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', '<strong>', $txt['online'], ': </strong>', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ', comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();

	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);

	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);

	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . ($context['num_spiders'] == 1 ? $txt['hidden'] : $txt['hidden_s']);

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '', '

				&nbsp;-&nbsp;', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong>&nbsp;-&nbsp;
				', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')<br>';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ': ', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<span class="membergroups">' . implode(', ', $context['membergroups']) . '</span>';
	}

	echo '
			</span></div></div>';
}

?>
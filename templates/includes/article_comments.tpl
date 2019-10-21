
	<table class="tdTitle" cellspacing="5" width="100%">
		<tbody>
		<tr>
			<td width="70px">ID</td>
			<td width="70px">Article ID</td>
			<td width="170px">Message</td>
			<td width="160px" style="text-align:right;">Posted By</td>
			<td width="135px" class="date">Date Added</td>
			<td width="135px" style="text-align:right;">Date Published</td>
			<td width="75px" style="text-align:right;">Status</td>
		</tr>
		</tbody>
	</table>

	<!-- BEGIN comment_record -->
	<table class="articleTable" cellspacing="5" width="100%">
		<tbody>
		<tr>
			<td width="70px"><a target="_blank" href="{discuss_article_url}">{discuss_comment_id}</a></td>
			<td width="70px">
				<a target="_blank" href="edit?article_id={discuss_article_id}" title="{discuss_article_title}">{discuss_article_id}</a>
			</td>
			<td width="170px">
				<div class="shortMessage">
					{discuss_comment_message_short}
					<!-- BEGIN discuss_comment_full_message_block -->
					<span class="readMore" style="color:#1589e1; cursor: pointer;"> {discuss_full_message_click_control}</span>
					<!-- END discuss_comment_full_message_block -->
				</div>
				<div class="full_message" style="display:none">{discuss_comment_message}</div>
			</td>
			<td width="160px" style="text-align:right;">{discuss_posted_by}</td>
			<td width="135px" class="date">{discuss_comment_date_added}</td>
			<td width="135px" style="text-align:right;">{discuss_comment_date_published}</td>
			<td width="75px" style="text-align:right; font-weight: bold; color:{discuss_status_color}">{discuss_comment_status}</td>
		</tr>
		</tbody>
	</table>
	<!-- END comment_record -->

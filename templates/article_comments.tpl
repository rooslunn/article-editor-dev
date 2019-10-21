<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Article Comments</title>
	<link rel="stylesheet" href="public/css/article-list.css" type="text/css"/>
	<script src="public/js/jquery.min.js"></script>
</head>
<body>
<div id="mainContainer">
	<div class="articleListTitle">
		<ul>
			<li class="homeLink">
				<a href="./">Home Page</a>
			</li>

		</ul>
	</div>

	<div class="articleLinks-container">
		<h1 id="header_title">{article_comments_title}</h1>

		<div class="buttonsBar">
			<span><a href="./article_comments">All</a></span>
			<span><a href="?status=new">New</a></span>
			<span><a href="?status=approved">Approved</a></span>
			<span><a href="?status=declined">Declined</a></span>
		</div>

		{article_comments}
	</div>
</div>
<script type="text/javascript">
	$(function(){
		$('.readMore').click(function(){
			$(this).hide().parents('td:first').find('.full_message, .shortMessage').toggle();
		});
	});
</script>
</body>
</html>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Article List</title>
        <link rel="stylesheet" href="public/css/article-list.css" type="text/css"/>
        <script src="public/js/jquery.min.js"></script>
    </head>
    <body>
        <div id="mainContainer">
            <div class="articleListTitle">
                <div class="articleId-searchbar">
                    <input type="search" placeholder="Search Article Id" id="search_article" onkeypress="return search(event);"/>
                </div>
                <ul>
                    <li class="homeLink">
                        <a href="./">Home Page</a>
                    </li>
                    <!-- BEGIN article_row_nav -->
                    <li>
                        <a href="?section_name={section_name}">{section_title} <p style="font-size:11px; display:inline;">({total})</p></a>
                    </li>
                    <!-- END article_row_nav -->
                </ul>
            </div>

            <div class="articleLinks-container">

                <h1>Doc Editor</h1>

<!------------------------ CIRCLES ----------------------------------------------->
 <!-- BEGIN article_editor_header -->
                <div class="circle"><div class="insideCircle"><h2>{published}</h2><a href="./article_list_status?status=published" id="{css_status_id}" class="status" style="color: green">Published</a></div></div>
                <div class="circle"><div class="insideCircle"><h2>{unpublished}</h2><a href="./article_list_status?status=unpublished"  id="{css_status_id}" class="status" style="color: orange">Unpublished</a></div></div>
                <div class="circle"><div class="insideCircle"><h2>{finished}</h2><a href="./article_list_status?status=finished"  id="{css_status_id}" class="status" style="color: black">Finished</a></div></div>
                <div class="circle"><div class="insideCircle"><h2>{hold}</h2><a href="./article_list_status?status=hold"  id="{css_status_id}" class="status" style="color: red">Hold</a></div></div>
<!--
                <div class="circle helpful" style="margin-left:35px;"><div class="insideCircle"><h2>{helpful}</h2><p id="{css_status_id}" class="status">Yes</p></div></div>
                <div class="circle helpful" style="margin-left:15px;"><div class="insideCircle"><h2>{unhelpful}</h2><p id="{css_status_id}" class="status">No</p></div></div>
-->
				<div class="circle helpful" style="margin-left:15px;"><div class="insideCircle"><h2>{untagged}</h2><p id="{css_status_id}" class="status"><a href="articles_missing_tags_list">Missing Tags</a></p></div></div>
 <!-- END article_editor_header -->
                <div class="circle">
					<div class="insideCircle"><h2>{comments_count}</h2><a href="article_comments" class="status" style="color: #9E15E1">Comments</a></div>
				</div
<!------------------------ /CIRCLES ----------------------------------------------->

                <div class="new-articleList">
                    <h2 class="subTitles-homePage"><a href="./new_articles_list">New Articles</a></h2>

                    <!-- BEGIN new_articles_row -->
                    <table class="articleTable" cellspacing="5" width="100%">
                        <tbody>
                            <tr>
                                <td width="55px" class="id" style="font-size:11px;"><a target="_blank" href="edit?article_id={article_id}">{article_id}</a></td>
                                <td class="name"><a target="_blank" href="{article_url}/?debug=1">{article_title}</a></td>
                                <td width="120px" class="date" style="font-size:11px;">{date_scanned}</td>
                                <td width="120px" id="{css_status_id}" class="status" style="font-size:11px;">{status}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- END new_articles_row -->
                </div>

                <div class="lastUpdated-articleList">
                    <h2 class="subTitles-homePage"><a href="./last_updated_articles_list">Last Updated</a></h2>

                    <!-- BEGIN last_updated_articles_row -->
                    <table class="articleTable" cellspacing="5" width="100%">
                        <tbody>
                            <tr>
                                <td width="55px" class="id" style="font-size:11px;"><a target="_blank" href="edit?article_id={article_id}">{article_id}</a></td>
                                <td class="name"><a target="_blank" href="{article_url}/?debug=1">{article_title}</a></td>
                                <td width="120px" class="date" style="font-size:11px;">{date_scanned}</td>
                                <td width="120px" id="{css_status_id}" class="status" style="font-size:11px;">{status}</td>
                            </tr>
                        </tbody>
                    </table>
                    <!-- END last_updated_articles_row -->
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function search(e) {
                if (e.keyCode == 13) {
                    var article_id = document.getElementById('search_article').value;
                    window.location.href = '?search&article_id=' + article_id;
                }
            }
            function set_status(status) {
                var articles = $('.article_status:checked');
                var values = [];
                for (var i = 0; i < articles.length; i++ ) {
                    values.push($(articles[i]).val());
                }
                $.ajax({
                    url: 'ajax/update_status/',
                    type: 'post',
                    data: {
                        values: values,
                        status: status
                    },
                    success: function(result) {
                        if (result == 1) {
                            location.reload()
                        } else {
                            alert('Database error!');
                        }
                    }
                });
            }

			$(function(){
				$('#selectAll').change(function(){
					console.log('clicked');
					$('input.article_status[type=checkbox]').prop('checked', $(this).is(':checked'));
				});
			});
        </script>
    </body>
</html>

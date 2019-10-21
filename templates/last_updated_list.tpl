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
                        <a href="./?section_name={section_name}">{section_title} <p style="font-size:11px; display:inline;">({total})</p></a>
                    </li>
                    <!-- END article_row_nav -->
                </ul>
            </div>
            <div class="articleLinks-container">
                
                <h1>Last Updated</h1>
                
                <div class="buttonsBar">
                    <!-- BEGIN edit_role -->
                    <label><input class="statusBtn" type="button" onclick="window.location.href = 'edit'" value="New" /></label>
                    <label><input class="statusBtn" type="button" onclick="set_status(1)" value="Finished" /></label>
                    <!-- BEGIN publish_role -->
                    <label><input class="statusBtn" type="button" onclick="set_status(3)" value="Publish" /></label>
                    <label><input class="statusBtn" type="button" onclick="set_status(5)" value="Unpublish" /></label>
                    <!-- END publish_role -->
                    <label><input class="statusBtn" type="button" onclick="set_status(4)" value="Hold" /></label>
                    <label><input class="statusBtn" type="button" onclick="set_status(2)" value="Delete" /></label>
                    <!-- END edit_role -->
                </div>

                <table class="tdTitle" cellspacing="5" width="100%">
                    <tbody>
                        <tr>
                            <td width="70px">ID</td>
                            <td>Title</td>
                            <td width="120px" class="date">Last Update</td>
                            <td width="120px" style="text-align:right;">Status</td>
                            <td width="20px" class="check"><input type="checkbox" name="select_all" id="selectAll"></td>
                        </tr>
                    </tbody>
                </table>

                <!-- BEGIN last_updated_articles_row -->
                <table class="articleTable" cellspacing="5" width="100%">
                    <tbody>
                        <tr>
                            <td width="55px" class="id" style="font-size:11px;"><a target="_blank" href="edit?article_id={article_id}">{article_id}</a></td>
                            <td class="name"><a target="_blank" href="{article_url}/?debug=1">{article_title}</a></td>
                            <td width="120px" class="date" style="font-size:11px;">{date_scanned}</td>
                            <td width="120px" id="{css_status_id}" class="status" style="font-size:11px;">{status}</td>
                            <td width="20px" class="check"><label><input type="checkbox" class="article_status" value="{article_id}"></label></td>
                        </tr>
                    </tbody>
                </table>
                <!-- END last_updated_articles_row -->

                <div class="buttonsBar btm">
                    <!-- BEGIN edit_role_footer -->
                    <label><input class="statusBtn" type="button" onclick="window.location.href = 'edit'" value="New" /></label>
                    <label><input class="statusBtn" type="button" onclick="set_status(1)" value="Finished" /></label>
					<!-- BEGIN publish_role_footer -->
                    <label><input class="statusBtn" type="button" onclick="set_status(3)" value="Publish" /></label>
                    <label><input class="statusBtn" type="button" onclick="set_status(5)" value="Unpublish" /></label>
					<!-- END publish_role_footer -->
                    <label><input class="statusBtn" type="button" onclick="set_status(4)" value="Hold" /></label>
                    <label><input class="statusBtn" type="button" onclick="set_status(2)" value="Delete" /></label>
                    <!-- END edit_role_footer -->
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

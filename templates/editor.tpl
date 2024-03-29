<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Editor</title>
        <link rel="stylesheet" href="public/css/editor.css" type="text/css"/>
        <link rel="stylesheet" href="public/css/codemirror.css" type="text/css"/>
        <link rel="stylesheet" href="public/css/multiple-select.css" type="text/css"/>
        <script type="text/javascript" src="public/js/jquery.min.js"></script>
        <script type="text/javascript" src="public/js/codemirror/codemirror.js"></script>
        <script type="text/javascript" src="public/js/codemirror/mode/htmlmixed/htmlmixed.js"></script>
        <script type="text/javascript" src="public/js/codemirror/mode/javascript/javascript.js"></script>
        <script type="text/javascript" src="public/js/codemirror/mode/xml/xml.js"></script>
        <script type="text/javascript" src="public/js/codemirror/mode/css/css.js"></script>
        <script type="text/javascript" src="public/js/jquery.multiple.select.js"></script>
    </head>
    <body>
        <!-- BEGIN issue_block -->
		<div id="errorWindow">
                    <div class="topContainer"><b>Article Issues</b></div>
            <div class="errorContainer">{article_issues}</div>
        </div>
		<!-- END issue_block -->
        <div id="mainContainer">

            <div class="topContainer">
                <fieldset>
                    <input type="search" id="search_article" placeholder="Edit Article Id" />
                    <p class="breadcrumbs"><a href="./">Home</a> > <a id="header_section" href="./?section_name={section_name}">{article_tags}</a> > <span id="header_title">{article_title}</span></p>
                </fieldset>
            </div>

            <div class="editorContainer">
                <div class="left">
                    <fieldset>
                        <input id="url" type="text" placeholder="URL" value="{article_url}"/>
                        <select id="doc_type">
                            <option value="article">Article</option>
                            <option value="guide" <!-- BEGIN guide_selected --> selected="selected" <!-- END guide_selected --> >Guide</option>
							<option value="tutorial" <!-- BEGIN tutorial_selected --> selected="selected" <!-- END tutorial_selected --> >Tutorial</option>
                        </select>
                        <select id="category">
                            <!-- BEGIN article_sections_row -->
                            <option value="{option_section_id}" {option_section_selected}>{option_section_title}</option>
                            <!-- END article_sections_row -->
                        </select>
                    </fieldset>
                    <fieldset>
                        <input id="title" type="text" placeholder="Title" value="{article_input_title}"/>
                    </fieldset>
                    <fieldset>
						<div>
                        	<textarea id="description" placeholder="Description" data-max-length="130">{article_description}</textarea>
						</div>
						<div id="count_symb_description" ></div>

                    </fieldset>
                    <fieldset class="editor">
                        <textarea id="editor">{article_content}</textarea>
                    </fieldset>
                    <fieldset style="padding-top:10px;">
                        <select id="status">
                            <!-- BEGIN article_status_row -->
                            <option value="{status_id}" {status_selected} style="color: {status_color}">{status_name}</option>
                            <!-- END article_status_row -->
                        </select>
                        <input type="hidden" id="action" value="{action}">
                        <input type="hidden" id="article_id" value="{article_id}">
                        <input type="hidden" id="image_ids" value="">

                        <div class="form-group" style="display:inline-block; width:130px;">
                            <select name="excluded_locale" id="ms_exclude_locale" multiple="multiple">
								<!-- BEGIN excluded_article_row -->
								<option value="{excluded_locale}" {excluded_locale_selected}>{excluded_locale_uc}</option>
								<!-- END excluded_article_row -->
                            </select>
                        </div>

                        <div class="form-group" style="display:inline-block; width:130px;">
                            <select name="excluded_resellers" id="ms_exclude_reseller" multiple="multiple">
                                <!-- BEGIN excluded_reseller_row -->
                                <option value="{exclude_reseller_id}" {excluded_reseller_selected}>{excluded_reseller_name}</option>
                                <!-- END excluded_reseller_row -->
                            </select>
                        </div>

                        <button id="save" style="width: 120px;">Save</button>
						<button id="preview" style="width: 120px;" onclick="window.open('preview?id={article_id}');" data-preview-link="preview?id={article_id}">
							Preview
						</button>
                    </fieldset>
                </div>
                <div class="right">
                    <fieldset>
                        <table id="infoBox" cellspacing="10">
                            <tr>
                                <td class="articleId tdTitle">Article ID</td>
                                <td class="tdInfo">{article_id}</td>
                            </tr>
                            <tr>
                                <td class="dateCreated tdTitle">Created</td>
                                <td class="tdInfo">{date_scanned}</td>
                            </tr>
                            <tr>
                                <td class="datePublished tdTitle">Published</td>
                                <td class="tdInfo">{date_published}</td>
                            </tr>
                            <tr>
                                <td class="dateUpdated tdTitle">Updated</td>
                                <td class="tdInfo">{date_updated}</td>
                            </tr>
                            <tr>
                                <td class="helpfull tdTitle">Helpfull</td>
                                <td class="tdInfo">{helpful} YES | {unhelpful} NO</td>
                            </tr>
                        </table>
                    </fieldset>
                    <div id="fileManagment">
                        <h5>File Management</h5>
                        <fieldset><input id="image" type="file" multiple /></fieldset>
                        <button id="upload">Upload images</button>
                        <ol class="imageName">
                            <!-- BEGIN article_images -->
                            <li>
                                <div>
									<div>
                                        <a href="get_image?id={image_id}" target="_blank">View</a>
                                        <span style="padding-left:4px">{image_name}</span>
									</div>
                                    <div class="closeBtn"><a href="" data-image-id="{image_id}" class="deleteImg">&#10006</a></div>
                                </div>
                            </li>
                            <!-- END article_images -->
                        </ol>
                    </div>
                    <div id="specialTags">
						<h5 style="margin-left:11px;">Special Tags</h5>
                        <table class="specialTags" cellspacing="10">
                            <tr>
                                <td width="150">Company Name</td>
                                <td>#reseller_name#</td>
                            </tr>
							<tr>
                                <td>Reseller Short Name</td>
                                <td>#reseller_short_name#</td>
                            </tr>
                            <tr>
                                <td>Support Email</td>
                                <td>#reseller_email#</td>
                            </tr>
							<tr>
                                <td>Site Locale</td>
                                <td>#locale#</td>
                            </tr>							
                        </table>
                    </div>
					<div>
						<h5 style="margin-left:11px;">Search Tags ({search_tags_count})</h5>
						<textarea style="margin-left: 7px; width: 290px; max-width: 290px; height: 268px;" name="article_search_tags">{article_search_tags}</textarea>
					</div>
                    <div class="form-group" style="display:inline-block; width:130px;padding-left: 5px;">
                        <select name="related_sections" id="ms_related_sections" multiple="multiple">
                            <!-- BEGIN related_section_row -->
                            <option value="{option_section_id}" {option_section_selected} {option_disabled}>{option_section_title}</option>
                            <!-- END related_section_row -->
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            var editor = document.getElementById('editor');
            var codemirror = CodeMirror.fromTextArea(editor, {mode: 'htmlmixed', lineNumbers: true, lineWrapping: true,  });
            codemirror.setSize(780, 580);

            var files;

            function generate_url_for_article() {
                var title = document.getElementById('title').value;
                document.getElementById('url').value = title.toLowerCase().replace(/[^a-zA-Z0-9]/g, '-').replace(/-+/g,'-').replace(/-+$/, '');
            }

            function search(e) {
                if (e.keyCode == 13) {
                    var article_id = document.getElementById('search_article').value;
                    window.location.href = '?article_id=' + article_id;
                }
            }

            function set_title() {
                document.getElementById('header_title').innerHTML = document.getElementById('title').value;
                if (document.getElementById('title').value == '') {
                    document.getElementById('header_title').innerHTML = 'Title';
                }
            }

            function get_article_data() {
				var images = $('#image_ids').val();

                return {
                    article_id : $('#article_id').val(),
                    article_url : $('#url').val(),
                    article_title : $('#title').val(),
                    article_description : $('#description').val(),
                    article_content : codemirror.getValue(),
                    article_status : $('#status').val(),
                    article_tags : $('#category').find('option:selected').text(),
					section_id : $('#category').val(),
                    doc_type : $('#doc_type').val(),
					article_search_tags : $('textarea[name=article_search_tags]').val(),
					linked_images : images ? images.split(',') : '',
					excluded_locales: $('[name=excluded_locale]').val(),
                    excluded_resellers: $('[name=excluded_resellers]').val(),
                    related_sections: $('[name=related_sections]').val()
                }
            }

            function save_article(action, button_type) {
                var id = 0, url, data = get_article_data();

                if (check_doc_type()) return false;

                switch (action) {
                    case 'save': url = './ajax/save_article/'; break;
                    case 'update': url = './ajax/update_article/'; break;
                    default : url = './ajax/update_article/'; break;
                }

                //if (button_type == 'save') data.article_status = 3;

                $.ajax({
                    url: url,
                    type: 'post',
                    data: data,
                    async: true,
                    success: function(result) {
						if (result > 0) {
							if (button_type == 'preview') {
								window.open('preview?id=' + result);
							}

                            window.location.href = '?article_id=' + result;
                            id = result;
                        } else {
                            //alert('Article can not be added or updated. Database error');
							alert(result);
                        }
                    }
                });

                return id;
            }

            function check_doc_type() {
                var result = false;

                $.ajax({
                    url: './ajax/check_doc_type',
                    type: 'post',
                    async: false,
                    data: {
                        doc_type : $('#doc_type').val(),
                        article_tags : $('#category').find('option:selected').text(),
                        article_id : $('#article_id').val()
                    },
                    dataType: 'json',
                    success: function(data) {
                        result = data;
                        if (data) {
							alert('You already have a guide!');
						}
                    }
                });

                return result;
            }

            function prepare_upload(event) {
                files = event.target.files;
            }

            function upload_images(event) {
                event.stopPropagation();
                event.preventDefault();

                var data = new FormData(),
					article_id = parseInt($('#article_id').val());

				if (files) {
					$.each(files, function(key, value) {
						data.append(key, value);
					});

					$.ajax({
						url: './ajax/upload_files?files&article_id=' + article_id,
						type: 'post',
						data: data,
						cache: false,
						dataType: 'json',
						processData: false,
						contentType: false,
						success: function(data) {
							if (data.files) {
								var image_ids = [], html = '', li;

								for (var i in data.files) {
									image_ids.push(i);
									html = 	'<div><div><a target="_blank" href="get_image?id=' + i + '">View </a>' +
									'<span style="padding-left:5px">' +  data.files[i]['image_name'] + '</span>' +
									'</div><div class="closeBtn"><a class="deleteImg" data-image-id="' +
									i + '" href="">&#10006</a></div></div>';

									li = $('<li></li>');
									li.html(html);
									$('ol.imageName').append(li);
								}
								if (!article_id) {
									$('#image_ids').val(image_ids);
								}
							}

							submit_form(event, data);
						}
					});
				} else {
					alert("You didn't select file for upload!");
				}
            }

			function delete_images(image_id, article_id) {
				article_id = article_id ? article_id : $('#article_id').val();
				$.ajax({
					url: './ajax/delete_files/',
					type: 'post',
					data: {image_id: image_id, article_id: article_id},
					cache: false,
					dataType: 'json',
					success: function(data){
						if (data['success']) {
							alert(data['success']);
							$('.deleteImg[data-image-id="' + image_id + '"]').parents('li').remove();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
					},
					complete: function() {
					}
				});

			}

            function submit_form(event, data) {
                $form = $(event.target);

                var form_data = $form.serialize();

                $.each(data.files, function(key, value) {
                    form_data = form_data + '&filenames[]=' + value;
                });

                $.ajax({
                    url: './ajax/upload_files/',
                    type: 'post',
                    data: form_data,
                    cache: false,
                    dataType: 'json',
                    success: function(data){
                        alert('Files successfully uploaded!');
						//location.reload();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                    },
                    complete: function() {
                    }
                });

            }

            $(document).ready(function(){
                $('#image').on('change', prepare_upload);

                $('#upload').on('click', upload_images);

                $('#saveForPreview').click(function() {
                    save_article($('#action').val(), 'preview');
                });

				$('body').on('click', '.deleteImg', function(event){
					event.preventDefault();
					delete_images($(this).data('image-id'));
				});


                $('#title').on('input', function() {
                    generate_url_for_article();
                    set_title();
                });

                $('#category').change(function() {
                    document.getElementById('header_section').innerHTML = $(this).find('option:selected').text();
                    var selected_section = $(this).val();

                    $('#ms_related_sections option').prop('disabled', false);

                    $('#ms_related_sections option[value=' + selected_section + ']').prop({
                        disabled: true,
                        selected: true
                    });

                    $('#ms_related_sections').multipleSelect('refresh');
                });

                $('#search_article').keyup(function(event) {
                    return search(event);
                });


                $('#save').click(function() {
                    save_article($('#action').val(), 'save');
                });

				$('#description').on('change keydown keyup paste', function(event){
					var max_length = $(this).data('max-length');
					$('#count_symb_description').text($(this).val().length + ' of ' + max_length);

					if ($(this).val().length >= max_length && $.inArray(event.keyCode, [8, 37, 38, 39, 40, 46])== -1){
						event.preventDefault();
					}
				});

				$('#count_symb_description').text($('#description').val().length + ' of ' + $('#description').data('max-length'));
            });

            $(function() {
                $('#ms_exclude_locale').multipleSelect({
                    width: '100%',
                    placeholder: 'Locales',
                    position: 'top'
                });

                $('#ms_exclude_reseller').multipleSelect({
                    width: '100%',
                    placeholder: 'Resellers',
                    position: 'top'
                });

                $('#ms_related_sections').multipleSelect({
                    width: 320,
                    placeholder: 'Sections',
                    position: 'top'
                });
            });
        </script>
    </body>
</html>

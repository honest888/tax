/* global jQuery FormData FileReader */
(function ($) {
    $.fn.uploader = function (options, testMode) {
        return this.each(function (index) {
            options = $.extend({
                submitButtonCopy: 'Upload Selected Files',
                instructionsCopy: 'Drag and Drop, or',
                furtherInstructionsCopy: 'Your can also drop more files, or',
                selectButtonCopy: 'Select Files',
                secondarySelectButtonCopy: 'Select More Files',
                dropZone: $(this),
                fileTypeWhiteList: ['jpg', 'png', 'jpeg', 'gif', 'pdf','csv','xlsx'],
                badFileTypeMessage: 'Sorry, unable to accept this type of file.',//we\'re 
                ajaxUrl: "/clients/ajax_upload",
                ajaxUrl2: "/clients/ajax_remove",
                testMode: false,
                pagename:'',
                link:'',
                fileary:''
            }, options);

            var state = {
                fileBatch: [],
                isUploading: false,
                isOverLimit: false,
                listIndex: 0
            };

            // create DOM elements
            var dom = {
                uploaderBox: $(this),
                submitButton: $('<button class="js-uploader__submit-button uploader__submit-button uploader__hide">' +
                    options.submitButtonCopy + '<i class="js-uploader__icon fa fa-upload uploader__icon"></i></button>'),
                instructions: $('<p class="js-uploader__instructions uploader__instructions">' +
                    options.instructionsCopy + '</p>'),
                selectButton: $('<input style="height: 0; width: 0;" id="fileinput' + index + '" type="file" multiple class="js-uploader__file-input uploader__file-input">' +
                    '<label for="fileinput' + index + '" style="cursor: pointer;" class="js-uploader__file-label uploader__file-label">' +
                    options.selectButtonCopy + '</label>'),
                secondarySelectButton: $('<input style="height: 0; width: 0;" id="secondaryfileinput' + index + '" type="file"' +
                    ' multiple class="js-uploader__file-input uploader__file-input">' +
                    '<label for="secondaryfileinput' + index + '" style="cursor: pointer;">'+
					'<div class="file-drop-zone" style="height: 240px;border:none; ">'+
					'<svg width="40" height="40" viewBox="0 0 68 68" fill="none" xmlns="http://www.w3.org/2000/svg">'+
						'<path d="M34 8.5C33.1716 8.5 32.5 9.17157 32.5 10C32.5 10.8284 33.1716 11.5 34 11.5V8.5ZM11.5 34C11.5 33.1716 10.8284 32.5 10 32.5C9.17157 32.5 8.5 33.1716 8.5 34H11.5ZM64.5 18V58H67.5V18H64.5ZM58 64.5H18V67.5H58V64.5ZM34 11.5H58V8.5H34V11.5ZM11.5 58V34H8.5V58H11.5ZM18 64.5C14.4101 64.5 11.5 61.5899 11.5 58H8.5C8.5 63.2467 12.7533 67.5 18 67.5V64.5ZM64.5 58C64.5 61.5899 61.5899 64.5 58 64.5V67.5C63.2467 67.5 67.5 63.2467 67.5 58H64.5ZM67.5 18C67.5 12.7533 63.2467 8.5 58 8.5V11.5C61.5899 11.5 64.5 14.4101 64.5 18H67.5Z" fill="#C5C5C5"></path>'+
						'<path d="M13 64L35.8375 40.249C37.0178 39.0214 38.9821 39.0214 40.1625 40.249L63 64" stroke="#C5C5C5" stroke-width="3"></path>'+
						'<circle cx="50" cy="26" r="5" stroke="#C5C5C5" stroke-width="3"></circle>'+
						'<circle cx="15" cy="15" r="15" fill="#C5C5C5"></circle>'+
						'<path d="M16.5 7C16.5 6.17157 15.8284 5.5 15 5.5C14.1716 5.5 13.5 6.17157 13.5 7L16.5 7ZM13.5 23C13.5 23.8284 14.1716 24.5 15 24.5C15.8284 24.5 16.5 23.8284 16.5 23L13.5 23ZM13.5 7L13.5 23L16.5 23L16.5 7L13.5 7Z" fill="currentColor"></path>'+
						'<path d="M23 16.5C23.8284 16.5 24.5 15.8284 24.5 15C24.5 14.1716 23.8284 13.5 23 13.5L23 16.5ZM7 13.5C6.17157 13.5 5.5 14.1716 5.5 15C5.5 15.8284 6.17157 16.5 7 16.5L7 13.5ZM23 13.5L7 13.5L7 16.5L23 16.5L23 13.5Z" fill="currentColor"></path>'+
					'</svg>'+
					'<b style="margin: 30px 0px 0px;">Drag &amp; Drop your files here or <span>Browse</span>'+
					'</b>'+
					'<p style="margin: 10px 0px 0px;">PDF, PNG, JPG, CSV, XLSX supported</p>'+
				'</div>'
					+'</label>'),
                fileList: $('<ul class="js-uploader__file-list uploader__file-list column gap-20" style="margin-top:20px;"></ul>'),
                contentsContainer: $('<div class="js-uploader__contents uploader__contents"></div>'),
                furtherInstructions: $('<div class="file-drop-zone" style="height: 240px;;"></div>')
            };

            // empty out whatever is in there
            dom.uploaderBox.empty();
            
            // create and attach UI elements
            if(options.fileary){
                setupDOM_server(dom);
            }else{
                setupDOM(dom);
            }
            
            // set up event handling
            bindUIEvents();

            function setupDOM (dom) {
                dom.contentsContainer
                    //.append(dom.instructions)
                    .append(dom.selectButton);
                dom.furtherInstructions
                    .append(dom.secondarySelectButton);
                dom.uploaderBox
				   // .append(dom.submitButton)
                    .append(dom.furtherInstructions)
                    .append(dom.fileList)
                   //.append(dom.contentsContainer);
            }
            function setupDOM_server (dom) {
                dom.contentsContainer
                    //.append(dom.instructions)
                    .append(dom.selectButton);
                dom.furtherInstructions
                    .append(dom.secondarySelectButton);
                dom.uploaderBox
				   // .append(dom.submitButton)
                    .append(dom.furtherInstructions)
                    .append(dom.fileList);
                  var  obj=options.fileary;
                  console.log('dom start');
                  var arr=[];
                for (var key in obj) {
                  arr.push(obj[key]);
                }
                for (var i = 0; i < arr.length; i++) {
                    addItem_server (arr[i]);
                }
                console.log('dom end');
                   //.append(dom.contentsContainer);
            }

            function bindUIEvents () {
                // handle drag and drop
                options.dropZone.on('dragover dragleave', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                });
                $.event.props.push('dataTransfer'); // jquery bug hack
                options.dropZone.on('drop', selectFilesHandler);

                // hack for being able selecting the same file name twice
                dom.selectButton.on('click', function () { this.value = null; });
                dom.selectButton.on('change', selectFilesHandler);
                dom.secondarySelectButton.on('click', function () { this.value = null; });
                dom.secondarySelectButton.on('change', selectFilesHandler);

                // handle the submit click
                dom.submitButton.on('click', uploadSubmitHandler);

                // remove link handler
                dom.uploaderBox.on('click', '.js-upload-remove-button', removeItemHandler);

                // expose handlers for testing
                if (options.testMode) {
                    options.dropZone.on('uploaderTestEvent', function (e) {
                        alert('c');
                        switch (e.functionName) {
                        case 'selectFilesHandler':
                            selectFilesHandler(e);
                            break;
                        case 'uploadSubmitHandler':
                            uploadSubmitHandler(e);
                            break;
                        default:
                            break;
                        }
                    });
                }
            }

            function addItem (file) {
                //var fileName = cleanName(file.name);
				var fileName = file.name;
                var fileSize = file.size;
                var id = state.listIndex;
                var sizeWrapper;
                var fileNameWrapper = $('<span class="uploader__file-list__text">' + fileName + '</span>');
              //jpg,png,jpeg,gif,pdf,csv,xlsx
              if (state.fileBatch.length !== 0) {
                  for (var i = 0; i < state.fileBatch.length; i++) {
                      if(state.fileBatch[i].fileName==fileName){  //文件重复者不上传
                          return false;
                      }
                  }
                }
                state.listIndex++;

                var listItem = $('<li class="uploader__file-list__item file-upload-item" data-index="' + id + '"></li>');
                var thumbnailContainer = $('<span class="uploader__file-list__thumbnail"></span>');
                var thumbnail = $('<img class="thumbnail" src="/static/stanford/images/del.svg">');
               // <i class="fa fa-spinner fa-spin uploader__icon--spinner"></i>var removeLink = $('<span class="uploader__file-list__button"><button class="uploader__icon-button js-upload-remove-button fa fa-times  delete-button" data-index="' + id + '" value="del"><svg class="delete-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 5C13.6569 5 15 6.34315 15 8H18.25C18.6642 8 19 8.33579 19 8.75C19 9.1297 18.7178 9.44349 18.3518 9.49315L18.25 9.5H17.454L16.1739 16.5192C16.0412 17.8683 14.9484 18.91 13.6126 18.9945L13.4371 19H10.5629C9.20734 19 8.06365 18.0145 7.84883 16.6934L7.82614 16.5192L6.545 9.5H5.75C5.3703 9.5 5.05651 9.21785 5.00685 8.85177L5 8.75C5 8.3703 5.28215 8.05651 5.64823 8.00685L5.75 8H9C9 6.34315 10.3431 5 12 5ZM10.5 8H13.5C13.5 7.17157 12.8284 6.5 12 6.5C11.1716 6.5 10.5 7.17157 10.5 8ZM8.052 9.5H15.947L14.6811 16.3724L14.6623 16.4982C14.5459 17.0751 14.0372 17.5 13.4371 17.5H10.5629L10.4358 17.4936C9.85033 17.4343 9.37768 16.9696 9.31893 16.3724L8.052 9.5Z" fill="currentColor"></path></svg></button></span>');
               var removeLink = $('<span class="uploader__file-list__button"><button class="uploader__icon-button js-upload-remove-button fa fa-times delete-button" data-index="' + id + '" value="del" style="background:url(/static/stanford/images/del.svg) no-repeat center;    background-color: #e5e5e5;"></button></span>');
                // validate the file
                if (options.fileTypeWhiteList.indexOf(getExtension(file.name).toLowerCase()) !== -1) {
                    // file is ok, add it to the batch
                    state.fileBatch.push({file: file, id: id, fileName: fileName, fileSize: fileSize});
                    sizeWrapper = $('<span class="uploader__file-list__size"></span>'); /*' + formatBytes(fileSize) + '*/
                } else {
                    return false;
                    // file is not ok, only add it to the dom
                    sizeWrapper = $('<span class="uploader__file-list__size"><span class="uploader__error">' + options.badFileTypeMessage + '</span></span>');
                }

                // create the thumbnail, if you can
               /* if (window.FileReader && file.type.indexOf('image') !== -1) {
                    var reader = new FileReader();
                    reader.onloadend = function () {
                        thumbnail.attr('src', '/static/stanford/images/file.svg');
                       // thumbnail.attr('src', reader.result);
                        thumbnail.parent().find('i').remove();
                    };
                    reader.onerror = function () {
                        thumbnail.remove();
                    };
                    reader.readAsDataURL(file);
                } else if (file.type.indexOf('image') === -1) {
                    thumbnail = $('<i class="fa fa-file-o uploader__icon">');
                }*/
                
                thumbnail.attr('src', '/static/stanford/images/file.svg');
                thumbnailContainer.append(thumbnail);
                listItem.append(thumbnailContainer);
                
                listItem
                    .append(fileNameWrapper)
                    .append(sizeWrapper)
                    .append(removeLink);
                
                dom.fileList.append(listItem);
                uploadSubmitHandler_single(file);
            }
            function addItem_server (file) {
                //var fileName = cleanName(file.name);
				var fileName = file;
                var fileSize = '';
                var id = state.listIndex;
                var sizeWrapper;
                var fileNameWrapper = $('<span class="uploader__file-list__text">' + fileName + '</span>');
              //jpg,png,jpeg,gif,pdf,csv,xlsx
              if (state.fileBatch.length !== 0) {
                  for (var i = 0; i < state.fileBatch.length; i++) {
                      if(state.fileBatch[i].fileName==fileName){  //文件重复者不上传
                          return false;
                      }
                  }
                }
                state.listIndex++;

                var listItem = $('<li class="uploader__file-list__item file-upload-item" data-index="' + id + '"></li>');
                var thumbnailContainer = $('<span class="uploader__file-list__thumbnail"></span>');
                var thumbnail = $('<img class="thumbnail" src="/static/stanford/images/del.svg">');
               var removeLink = $('<span class="uploader__file-list__button"><button class="uploader__icon-button js-upload-remove-button fa fa-times delete-button" data-index="' + id + '" value="del" style="background:url(/static/stanford/images/del.svg) no-repeat center;    background-color: #e5e5e5;"></button></span>');
                // validate the file
                state.fileBatch.push({file: file, id: id, fileName: fileName, fileSize: fileSize});
                sizeWrapper = $('<span class="uploader__file-list__size"></span>');
                
                thumbnail.attr('src', '/static/stanford/images/file.svg');
                thumbnailContainer.append(thumbnail);
                listItem.append(thumbnailContainer);
                
                listItem
                    .append(fileNameWrapper)
                    .append(sizeWrapper)
                    .append(removeLink);
                
                dom.fileList.append(listItem);
            }

            function getExtension (path) {
                var basename = path.split(/[\\/]/).pop();
                var pos = basename.lastIndexOf('.');

                if (basename === '' || pos < 1) {
                    return '';
                }
                return basename.slice(pos + 1);
            }

            function formatBytes (bytes, decimals) {
                if (bytes === 0) return '0 Bytes';
                var k = 1024;
                var dm = decimals + 1 || 3;
                var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                var i = Math.floor(Math.log(bytes) / Math.log(k));
                return (bytes / Math.pow(k, i)).toPrecision(dm) + ' ' + sizes[i];
            }

            function cleanName (name) {
                name = name.replace(/\s+/gi, '-'); // Replace white space with dash
                return name.replace(/[^a-zA-Z0-9.\-]/gi, ''); // Strip any special characters
            }
            function uploadSubmitHandler_single (flie) {
               
                 var data = new FormData();
                 data.append('pagename',options.pagename);
                 data.append('link',options.link);
                 data.append('files', flie);
                 // data.append('files', state.fileBatch[i].file, state.fileBatch[i].fileName);
                 console.log(data);
                 $.ajax({
                        type: 'POST',
                        url: options.ajaxUrl,
                        data: data,
                        async: false,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success:function (res) {
                          if (res.code==1){
                                var html='<div class="save-indicator font-14 bold row align-center justify-between gap-6 radius-4 pv-12 ph-12 save-indicator--" style="color: var(--color-darkgrey);"><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"></path></svg>'+res.savetime+'</div>';
                                $('#savetime').html(html);
                           }else {
                               msg(res.msg);
                          }
                         },error:function () {
                             msg('Try it again later!')
                        }
                    });
            } 
            function uploadSubmitHandler () {
                if (state.fileBatch.length !== 0) {
                    var data = new FormData();
                    for (var i = 0; i < state.fileBatch.length; i++) {
                        data.append('files[]', state.fileBatch[i].file, state.fileBatch[i].fileName);
                    }
                    $.ajax({
                        type: 'POST',
                        url: options.ajaxUrl,
                        data: data,
                        cache: false,
                        contentType: false,
                        processData: false
                    });
                }
            }

            function selectFilesHandler (e) {
                e.preventDefault();
                e.stopPropagation();

                if (!state.isUploading) {
                    // files come from the input or a drop
                    var files = e.target.files || e.dataTransfer.files || e.dataTransfer.getData;

                    // process each incoming file
                    for (var i = 0; i < files.length; i++) {
                        addItem(files[i]);
                    }
                }
                renderControls();
            }

            function renderControls () {
                //alert(dom.fileList.children().size());
                    dom.submitButton.removeClass('uploader__hide');
                    dom.furtherInstructions.removeClass('uploader__hide');
                    dom.contentsContainer.addClass('uploader__hide');
               /* if (dom.fileList.children().size() !== 0) {
                    dom.submitButton.removeClass('uploader__hide');
                    dom.furtherInstructions.removeClass('uploader__hide');
                    dom.contentsContainer.addClass('uploader__hide');
                } else {
                    dom.submitButton.addClass('uploader__hide');
                    dom.furtherInstructions.addClass('uploader__hide');
                    dom.contentsContainer.removeClass('uploader__hide');
                }*/
            }

            function removeItemHandler (e) {
                e.preventDefault();

                if (!state.isUploading) {
                    var removeIndex = $(e.target).data('index');
                    
                     var data22 = new FormData();
                     data22.append('pagename',options.pagename);
                     data22.append('link',options.link);
                    for (var i = 0; i < state.fileBatch.length; i++) {
                        if(state.fileBatch[i].id==removeIndex){
                          //  getExtension(file.name)
                            data22.append('files', state.fileBatch[i].fileName); 
                           // data22.append('files', state.fileBatch[i].file, state.fileBatch[i].fileName); 
                            $.ajax({
                            type: 'POST',
                            url: options.ajaxUrl2,
                            data: data22,
                            async: false,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success:function (res) {
                              if (res.code==1){
                                    var html='<div class="save-indicator font-14 bold row align-center justify-between gap-6 radius-4 pv-12 ph-12 save-indicator--" style="color: var(--color-darkgrey);"><svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"><path fill="currentColor" d="M20.285 2l-11.285 11.567-5.286-5.011-3.714 3.716 9 8.728 15-15.285z"></path></svg>'+res.savetime+'</div>';
                                    $('#savetime').html(html);
                               }else {
                                   msg(res.msg);
                              }
                             },error:function () {
                                 msg('Try it again later!')
                            }
                        });
                            break;
                        }
                    }
                    //处理删除 
                  
                     // data22.append('filename', state.fileBatch[removeIndex].fileName);
                    //处理删除 
                    removeItem(removeIndex); //页面删除 
                    $(e.target).parent().remove();
                }

                renderControls();
            }

            function removeItem (id) {
                // remove from the batch
                for (var i = 0; i < state.fileBatch.length; i++) {
                    if (state.fileBatch[i].id === parseInt(id)) {
                        state.fileBatch.splice(i, 1);
                        break;
                    }
                }
                // remove from the DOM
                dom.fileList.find('li[data-index="' + id + '"]').remove();
            }
        });
    };
}(jQuery));
/** Wonderplugin Carousel Plugin Free Version
 * Copyright 2015 Magic Hills Pty Ltd All Rights Reserved
 * Website: http://www.wonderplugin.com
 * Version 5.0
 */
(function ($) {
    $(document).ready(function () {
        $(".wonderplugin-engine").css({display: "none"});
        $("#wonderplugin-carousel-toolbar").find("li").each(function (index) {
            $(this).click(function () {
                if ($(this).hasClass("wonderplugin-tab-buttons-selected"))return;
                $(this).parent().find("li").removeClass("wonderplugin-tab-buttons-selected");
                if (!$(this).hasClass("laststep"))$(this).addClass("wonderplugin-tab-buttons-selected");
                $("#wonderplugin-carousel-tabs").children("li").removeClass("wonderplugin-tab-selected");
                $("#wonderplugin-carousel-tabs").children("li").eq(index).addClass("wonderplugin-tab-selected");
                $("#wonderplugin-carousel-tabs").removeClass("wonderplugin-tabs-grey");
                if (index == 3) {
                    previewCarousel();
                    $("#wonderplugin-carousel-tabs").addClass("wonderplugin-tabs-grey")
                } else if (index == 4)publishCarousel()
            })
        });
        var getURLParams = function (href) {
            var result = {};
            if (href.indexOf("?") < 0)return result;
            var params = href.substring(href.indexOf("?") + 1).split("&");
            for (var i = 0; i < params.length; i++) {
                var value = params[i].split("=");
                if (value && value.length == 2 && value[0].toLowerCase() != "v")result[value[0].toLowerCase()] = value[1]
            }
            return result
        };
        var slideDialog = function (dialogType, onSuccess, data, dataIndex) {
            var dialogTitle = ["image", "video", "Youtube Video", "Vimeo Video"];
            var dialogCode = "<div class='wonderplugin-dialog-container'>" + "<div class='wonderplugin-dialog-bg'></div>" + "<div class='wonderplugin-dialog'>" + "<h3 id='wonderplugin-dialog-title'></h3>" + "<div class='error' id='wonderplugin-dialog-error' style='display:none;'></div>" + "<table id='wonderplugin-dialog-form'>";
            if (dialogType == 2 || dialogType == 3)dialogCode += "<tr>" + "<th>Enter video URL</th>" +
                "<td><input name='wonderplugin-dialog-video' type='text' id='wonderplugin-dialog-video' value='' class='regular-text' /> <input type='button' class='button' id='wonderplugin-dialog-select-video' value='Enter' /></td>" + "</tr>" + "<tr>";
            dialogCode += "<tr>" + "<th>Enter" + (dialogType > 0 ? " poster" : "") + " image URL</th>" + "<td><input name='wonderplugin-dialog-image' type='text' id='wonderplugin-dialog-image' value='' class='regular-text' /> or <input type='button' class='button' data-textid='wonderplugin-dialog-image' id='wonderplugin-dialog-select-image' value='Upload' /></td>" +
                "</tr>" + "<tr id='wonderplugin-dialog-image-display-tr' style='display:none;'>" + "<th></th>" + "<td><img id='wonderplugin-dialog-image-display' style='width:80px;height:80px;' /></td>" + "</tr>" + "<tr>" + "<th>Thumbnail URL</th>" + "<td>" + "<input name='wonderplugin-dialog-thumbnail' type='text' id='wonderplugin-dialog-thumbnail' value='' class='regular-text' /> or <input type='button' class='button' data-textid='wonderplugin-dialog-thumbnail' id='wonderplugin-dialog-select-thumbnail' value='Upload' /> <br/>" +
                "<label><input name='wonderplugin-dialog-displaythumbnail' type='checkbox' id='wonderplugin-dialog-displaythumbnail' value='' />Use thumbnail in carousel</label>" + "</td>" + "</tr>";
            if (dialogType == 1)dialogCode += "<tr>" + "<th>MP4 video URL</th>" + "<td><input name='wonderplugin-dialog-mp4' type='text' id='wonderplugin-dialog-mp4' value='' class='regular-text' /> or <input type='button' class='button' data-textid='wonderplugin-dialog-mp4' id='wonderplugin-dialog-select-mp4' value='Upload' /></td>" + "</tr>" +
                "<tr>" + "<tr>" + "<th>WebM video URL (Optional)</th>" + "<td><input name='wonderplugin-dialog-webm' type='text' id='wonderplugin-dialog-webm' value='' class='regular-text' /> or <input type='button' class='button' data-textid='wonderplugin-dialog-webm' id='wonderplugin-dialog-select-webm' value='Upload' /></td>" + "</tr>" + "<tr>";
            dialogCode += "<tr>" + "<th>Title</th>" + "<td><input name='wonderplugin-dialog-image-title' type='text' id='wonderplugin-dialog-image-title' value='' class='large-text' /></td>" + "</tr>" +
                "<tr>" + "<th>Description</th>" + "<td><textarea name='wonderplugin-dialog-image-description' type='' id='wonderplugin-dialog-image-description' value='' class='large-text' /></td>" + "</tr>";
            dialogCode += "<tr>" + "<th>Click to open Lightbox popup</th>" + "<td><label><input name='wonderplugin-dialog-lightbox' type='checkbox' id='wonderplugin-dialog-lightbox' value='' checked /> Open current " + dialogTitle[dialogType] + " in Lightbox</label>" + "</tr>" + "<tr><th>Lightbox size</th>" + "<td><label><input name='wonderplugin-dialog-lightbox-size' type='checkbox' id='wonderplugin-dialog-lightbox-size' value='' /> Set Lightbox size (px) </label>" +
                " <input name='wonderplugin-dialog-lightbox-width' type='text' id='wonderplugin-dialog-lightbox-width' value='640' class='small-text' /> / <input name='wonderplugin-dialog-lightbox-height' type='text' id='wonderplugin-dialog-lightbox-height' value='480' class='small-text' />" + "</td>" + "</tr>";
            if (dialogType == 0)dialogCode += "<tr><th>Or click to open web link</th>" + "<td>" + "<input name='wonderplugin-dialog-weblink' type='text' id='wonderplugin-dialog-weblink' value='' class='regular-text' disabled /><br />Uncheck the option \"Open current image in Lightbox\" to enable weblink" +
                "</td>" + "</tr>" + "<tr><th>Set web link target</th>" + "<td>" + "<input name='wonderplugin-dialog-linktarget' type='text' id='wonderplugin-dialog-linktarget' value='' class='regular-text' disabled />" + "</td>" + "</tr>" + "<tr><th></th>" + "<td>" + "<label><input name='wonderplugin-dialog-weblinklightbox' type='checkbox' id='wonderplugin-dialog-weblinklightbox' value='' /> Open web link in Lightbox</label>" + "</td>" + "</tr>";
            dialogCode += "</table>" + "<br /><br />" + "<div class='wonderplugin-dialog-buttons'>" + "<input type='button' class='button button-primary' id='wonderplugin-dialog-ok' value='OK' />" +
                "<input type='button' class='button' id='wonderplugin-dialog-cancel' value='Cancel' />" + "</div>" + "</div>" + "</div>";
            var $slideDialog = $(dialogCode);
            $("body").append($slideDialog);
            $(".wonderplugin-dialog").css({"margin-top": String($(document).scrollTop() + 60) + "px"});
            $(".wonderplugin-dialog-bg").css({height: $(document).height() + "px"});
            $("#wonderplugin-dialog-lightbox").click(function () {
                var is_checked = $(this).is(":checked");
                if ($("#wonderplugin-dialog-weblink").length)$("#wonderplugin-dialog-weblink").attr("disabled",
                    is_checked);
                if ($("#wonderplugin-dialog-linktarget").length)$("#wonderplugin-dialog-linktarget").attr("disabled", is_checked);
                if ($("#wonderplugin-dialog-weblinklightbox").length)$("#wonderplugin-dialog-weblinklightbox").attr("disabled", is_checked)
            });
            $(".wonderplugin-dialog").css({"margin-top": String($(document).scrollTop() + 60) + "px"});
            $(".wonderplugin-dialog-bg").css({height: $(document).height() + "px"});
            $("#wonderplugin-dialog-title").html("Add " + dialogTitle[dialogType]);
            if (data) {
                if (dialogType == 2 || dialogType ==
                    3)$("#wonderplugin-dialog-video").val(data.video);
                $("#wonderplugin-dialog-image").val(data.image);
                if (data.image) {
                    $("#wonderplugin-dialog-image-display-tr").css({display: "table-row"});
                    $("#wonderplugin-dialog-image-display").attr("src", data.image)
                }
                $("#wonderplugin-dialog-thumbnail").val(data.thumbnail);
                if (data.displaythumbnail)$("#wonderplugin-dialog-displaythumbnail").attr("checked", true); else $("#wonderplugin-dialog-displaythumbnail").attr("checked", false);
                $("#wonderplugin-dialog-image-title").val(data.title);
                $("#wonderplugin-dialog-image-description").val(data.description);
                if (dialogType == 1) {
                    $("#wonderplugin-dialog-mp4").val(data.mp4);
                    $("#wonderplugin-dialog-webm").val(data.webm)
                }
                if (dialogType == 0) {
                    $("#wonderplugin-dialog-weblink").val(data.weblink);
                    $("#wonderplugin-dialog-linktarget").val(data.linktarget);
                    $("#wonderplugin-dialog-weblink").attr("disabled", data.lightbox);
                    $("#wonderplugin-dialog-linktarget").attr("disabled", data.lightbox);
                    if (data.weblinklightbox)$("#wonderplugin-dialog-weblinklightbox").attr("checked",
                        true); else $("#wonderplugin-dialog-weblinklightbox").attr("checked", false);
                    $("#wonderplugin-dialog-weblinklightbox").attr("disabled", data.lightbox)
                }
                if ("lightbox"in data)$("#wonderplugin-dialog-lightbox").attr("checked", data.lightbox);
                if ("lightboxsize"in data)$("#wonderplugin-dialog-lightbox-size").attr("checked", data.lightboxsize);
                if (data.lightboxwidth)$("#wonderplugin-dialog-lightbox-width").val(data.lightboxwidth);
                if (data.lightboxheight)$("#wonderplugin-dialog-lightbox-height").val(data.lightboxheight)
            }
            if (dialogType ==
                2 || dialogType == 3)$("#wonderplugin-dialog-select-video").click(function () {
                var videoData = {type: dialogType, video: $.trim($("#wonderplugin-dialog-video").val()), image: $.trim($("#wonderplugin-dialog-image").val()), thumbnail: $.trim($("#wonderplugin-dialog-thumbnail").val()), title: $.trim($("#wonderplugin-dialog-image-title").val()), description: $.trim($("#wonderplugin-dialog-image-description").val())};
                $slideDialog.remove();
                onlineVideoDialog(dialogType, function (items) {
                    items.map(function (data) {
                        wonderplugin_carousel_config.slides.push({type: dialogType,
                            image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image, displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm, title: data.title, description: data.description, weblink: data.weblink, linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight})
                    });
                    updateMediaTable()
                }, videoData, true, dataIndex)
            });
            var media_upload_onclick = function (event) {
                event.preventDefault();
                var buttonId = $(this).attr("id");
                var textId = $(this).data("textid");
                var media_uploader = wp.media.frames.file_frame = wp.media({title: "Choose Image", button: {text: "Choose Image"}, multiple: dialogType == 0 && buttonId == "wonderplugin-dialog-select-image"});
                media_uploader.on("select", function (event) {
                    var selection = media_uploader.state().get("selection");
                    if (dialogType == 0 && buttonId == "wonderplugin-dialog-select-image" && selection.length > 1) {
                        var items = [];
                        selection.map(function (attachment) {
                            attachment = attachment.toJSON();
                            if (attachment.type != "image")return;
                            var thumbnail;
                            var thumbnailsize = $("#wonderplugin-carousel-thumbnailsize").text();
                            if (thumbnailsize && thumbnailsize.length > 0 && attachment.sizes && attachment.sizes[thumbnailsize] && attachment.sizes[thumbnailsize].url)thumbnail = attachment.sizes[thumbnailsize].url; else if (attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url)thumbnail = attachment.sizes.medium.url; else if (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url)thumbnail =
                                attachment.sizes.thumbnail.url; else thumbnail = attachment.url;
                            items.push({image: attachment.url, thumbnail: thumbnail, displaythumbnail: false, title: attachment.title, description: attachment.description, weblink: "", weblinklightbox: false, linktarget: "", lightbox: true, lightboxsize: false, lightboxwidth: 640, lightboxheight: 480})
                        });
                        $slideDialog.remove();
                        onSuccess(items)
                    } else {
                        attachment = selection.first().toJSON();
                        if (buttonId == "wonderplugin-dialog-select-image") {
                            if (attachment.type != "image") {
                                $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select an image file</p>");
                                return
                            }
                            var thumbnail;
                            var thumbnailsize = $("#wonderplugin-carousel-thumbnailsize").text();
                            if (thumbnailsize && thumbnailsize.length > 0 && attachment.sizes && attachment.sizes[thumbnailsize] && attachment.sizes[thumbnailsize].url)thumbnail = attachment.sizes[thumbnailsize].url; else if (attachment.sizes && attachment.sizes.medium && attachment.sizes.medium.url)thumbnail = attachment.sizes.medium.url; else if (attachment.sizes && attachment.sizes.thumbnail && attachment.sizes.thumbnail.url)thumbnail = attachment.sizes.thumbnail.url;
                            else thumbnail = attachment.url;
                            $("#wonderplugin-dialog-image-display-tr").css({display: "table-row"});
                            $("#wonderplugin-dialog-image-display").attr("src", attachment.url);
                            $("#wonderplugin-dialog-image").val(attachment.url);
                            $("#wonderplugin-dialog-thumbnail").val(thumbnail);
                            if ($.trim($("#wonderplugin-dialog-image-title").val()).length <= 0)$("#wonderplugin-dialog-image-title").val(attachment.title);
                            if ($.trim($("#wonderplugin-dialog-image-description").val()).length <= 0)$("#wonderplugin-dialog-image-description").val(attachment.description)
                        } else if (buttonId ==
                            "wonderplugin-dialog-select-thumbnail") {
                            if (attachment.type != "image") {
                                $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select an image file</p>");
                                return
                            }
                            $("#wonderplugin-dialog-thumbnail").val(attachment.url)
                        } else {
                            if (attachment.type != "video") {
                                $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select a video file</p>");
                                return
                            }
                            $("#" + textId).val(attachment.url)
                        }
                    }
                    $("#wonderplugin-dialog-error").css({display: "none"}).empty()
                });
                media_uploader.open()
            };
            if (parseInt($("#wonderplugin-carousel-wp-history-media-uploader").text()) ==
                1) {
                var buttonId = "";
                var textId = "";
                var history_media_upload_onclick = function (event) {
                    buttonId = $(this).attr("id");
                    textId = $(this).data("textid");
                    var mediaType = buttonId == "wonderplugin-dialog-select-image" || buttonId == "wonderplugin-dialog-select-thumbnail" ? "image" : "video";
                    tb_show("Upload " + mediaType, "media-upload.php?referer=wonderplugin-carousel&type=" + mediaType + "&TB_iframe=true", false);
                    return false
                };
                window.send_to_editor = function (html) {
                    tb_remove();
                    if (buttonId == "wonderplugin-dialog-select-image") {
                        var $img =
                            $("img", html);
                        if (!$img.length) {
                            $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select an image file</p>");
                            return
                        }
                        var thumbnail = $img.attr("src");
                        var src = $(html).is("a") ? $(html).attr("href") : thumbnail;
                        $("#wonderplugin-dialog-image-display-tr").css({display: "table-row"});
                        $("#wonderplugin-dialog-image-display").attr("src", thumbnail);
                        $("#wonderplugin-dialog-image").val(src);
                        $("#wonderplugin-dialog-thumbnail").val(thumbnail);
                        if ($.trim($("#wonderplugin-dialog-image-title").val()).length <=
                            0)$("#wonderplugin-dialog-image-title").val($("img", html).attr("title"))
                    } else if (buttonId == "wonderplugin-dialog-select-thumbnail") {
                        var $img = $("img", html);
                        if (!$img.length) {
                            $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select an image file</p>");
                            return
                        }
                        var src = $(html).is("a") ? $(html).attr("href") : $img.attr("src");
                        $("#wonderplugin-dialog-thumbnail").val(src)
                    } else {
                        if ($("img", html).length) {
                            $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select a video file</p>");
                            return
                        }
                        $("#" + textId).val($(html).attr("href"))
                    }
                    $("#wonderplugin-dialog-error").css({display: "none"}).empty()
                };
                $("#wonderplugin-dialog-select-image").click(history_media_upload_onclick);
                $("#wonderplugin-dialog-select-thumbnail").click(history_media_upload_onclick);
                if (dialogType == 1) {
                    $("#wonderplugin-dialog-select-mp4").click(history_media_upload_onclick);
                    $("#wonderplugin-dialog-select-webm").click(history_media_upload_onclick)
                }
            } else {
                $("#wonderplugin-dialog-select-image").click(media_upload_onclick);
                $("#wonderplugin-dialog-select-thumbnail").click(media_upload_onclick);
                if (dialogType == 1) {
                    $("#wonderplugin-dialog-select-mp4").click(media_upload_onclick);
                    $("#wonderplugin-dialog-select-webm").click(media_upload_onclick)
                }
            }
            $("#wonderplugin-dialog-ok").click(function () {
                if ($.trim($("#wonderplugin-dialog-image").val()).length <= 0) {
                    $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select an image file</p>");
                    return
                }
                if (dialogType == 1 && $.trim($("#wonderplugin-dialog-mp4").val()).length <=
                    0) {
                    $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please select a video file</p>");
                    return
                }
                var item = {image: $.trim($("#wonderplugin-dialog-image").val()), thumbnail: $.trim($("#wonderplugin-dialog-thumbnail").val()), displaythumbnail: $("#wonderplugin-dialog-displaythumbnail").is(":checked"), video: $.trim($("#wonderplugin-dialog-video").val()), mp4: $.trim($("#wonderplugin-dialog-mp4").val()), webm: $.trim($("#wonderplugin-dialog-webm").val()), title: $.trim($("#wonderplugin-dialog-image-title").val()),
                    description: $.trim($("#wonderplugin-dialog-image-description").val()), weblink: $.trim($("#wonderplugin-dialog-weblink").val()), linktarget: $.trim($("#wonderplugin-dialog-linktarget").val()), weblinklightbox: $("#wonderplugin-dialog-weblinklightbox").is(":checked"), lightbox: $("#wonderplugin-dialog-lightbox").is(":checked"), lightboxsize: $("#wonderplugin-dialog-lightbox-size").is(":checked"), lightboxwidth: parseInt($.trim($("#wonderplugin-dialog-lightbox-width").val())), lightboxheight: parseInt($.trim($("#wonderplugin-dialog-lightbox-height").val()))};
                $slideDialog.remove();
                onSuccess([item])
            });
            $("#wonderplugin-dialog-cancel").click(function () {
                $slideDialog.remove()
            })
        };
        var onlineVideoDialog = function (dialogType, onSuccess, videoData, invokeFromSlideDialog, dataIndex) {
            var dialogTitle = ["Image", "Video", "Youtube Video", "Vimeo Video"];
            var dialogExample = ["", "", "https://www.youtube.com/watch?v=wswxQ3mhwqQ", "https://vimeo.com/1084537"];
            var dialogCode = "<div class='wonderplugin-dialog-container'>" + "<div class='wonderplugin-dialog-bg'></div>" + "<div class='wonderplugin-dialog'>" +
                "<h3 id='wonderplugin-dialog-title'></h3>" + "<div class='error' id='wonderplugin-dialog-error' style='display:none;'></div>" + "<table id='wonderplugin-dialog-form'>" + "<tr>" + "<th>Enter " + dialogTitle[dialogType] + " URL</th>" + "<td><input name='wonderplugin-dialog-video' type='text' id='wonderplugin-dialog-video' value='' class='regular-text' />" + "<p>URL Example: " + dialogExample[dialogType] + "<p>" + "</td>" + "</tr>";
            dialogCode += "</table>" + "<div id='wonderplugin-carousel-video-dialog-loading'></div>" + "<div class='wonderplugin-dialog-buttons'>" +
                "<input type='button' class='button button-primary' id='wonderplugin-dialog-ok' value='OK' />" + "<input type='button' class='button' id='wonderplugin-dialog-cancel' value='Cancel' />" + "</div>" + "</div>" + "</div>";
            var $videoDialog = $(dialogCode);
            $("body").append($videoDialog);
            $(".wonderplugin-dialog").css({"margin-top": String($(document).scrollTop() + 60) + "px"});
            $(".wonderplugin-dialog-bg").css({height: $(document).height() + "px"});
            if (!videoData)videoData = {type: dialogType};
            $("#wonderplugin-dialog-title").html("Add " +
                dialogTitle[dialogType]);
            var videoDataReturn = function () {
                $videoDialog.remove();
                slideDialog(dialogType, function (items) {
                    if (items && items.length > 0) {
                        if (typeof dataIndex !== "undefined" && dataIndex >= 0)wonderplugin_carousel_config.slides.splice(dataIndex, 1);
                        items.map(function (data) {
                            var result = {type: dialogType, image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image, displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm, title: data.title, description: data.description, weblink: data.weblink,
                                linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight};
                            if (typeof dataIndex !== "undefined" && dataIndex >= 0)wonderplugin_carousel_config.slides.splice(dataIndex, 0, result); else wonderplugin_carousel_config.slides.push(result)
                        });
                        updateMediaTable()
                    }
                }, videoData, dataIndex)
            };
            $("#wonderplugin-dialog-ok").click(function () {
                var href = $.trim($("#wonderplugin-dialog-video").val());
                if (href.length <=
                    0) {
                    $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please enter a " + dialogTitle[dialogType] + " URL</p>");
                    return
                }
                var protocol = window.location.protocol == "https:" ? "https:" : "http:";
                if (dialogType == 2) {
                    var youtubeId = "";
                    var regExp = /^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/;
                    var match = href.match(regExp);
                    if (match && match[7] && match[7].length == 11)youtubeId = match[7]; else {
                        $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please enter a valid Youtube URL</p>");
                        return
                    }
                    var result = protocol + "//www.youtube.com/embed/" + youtubeId;
                    var params = getURLParams(href);
                    var first = true;
                    for (var key in params) {
                        if (first) {
                            result += "?";
                            first = false
                        } else result += "&";
                        result += key + "=" + params[key]
                    }
                    videoData.video = result;
                    videoData.image = protocol + "//img.youtube.com/vi/" + youtubeId + "/0.jpg";
                    videoData.thumbnail = protocol + "//img.youtube.com/vi/" + youtubeId + "/1.jpg";
                    videoDataReturn()
                } else if (dialogType == 3) {
                    var vimeoId = "";
                    var regExp = /^.*(vimeo\.com\/)((video\/)|(channels\/[A-z]+\/)|(groups\/[A-z]+\/videos\/))?([0-9]+)/;
                    var match = href.match(regExp);
                    if (match && match[6])vimeoId = match[6]; else {
                        $("#wonderplugin-dialog-error").css({display: "block"}).html("<p>Please enter a valid Vimeo URL</p>");
                        return
                    }
                    var result = protocol + "//player.vimeo.com/video/" + vimeoId;
                    var params = getURLParams(href);
                    var first = true;
                    for (var key in params) {
                        if (first) {
                            result += "?";
                            first = false
                        } else result += "&";
                        result += key + "=" + params[key]
                    }
                    videoData.video = result;
                    $("#wonderplugin-carousel-video-dialog-loading").css({display: "block"});
                    $.ajax({url: protocol + "//www.vimeo.com/api/v2/video/" +
                        vimeoId + ".json?callback=?", dataType: "json", timeout: 3E3, data: {format: "json"}, success: function (data) {
                        videoData.image = data[0].thumbnail_large;
                        videoData.thumbnail = data[0].thumbnail_medium;
                        videoDataReturn()
                    }, error: function () {
                        videoDataReturn()
                    }})
                }
            });
            $("#wonderplugin-dialog-cancel").click(function () {
                $videoDialog.remove();
                if (invokeFromSlideDialog)videoDataReturn()
            })
        };
        var updateMediaTable = function () {
            var mediaType = ["Image", "Video", "YouTube", "Vimeo"];
            $("#wonderplugin-carousel-media-table").empty();
            for (var i =
                0; i < wonderplugin_carousel_config.slides.length; i++)$("#wonderplugin-carousel-media-table").append("<li>" + "<div class='wonderplugin-carousel-media-table-id'>" + (i + 1) + "</div>" + "<div class='wonderplugin-carousel-media-table-img'>" + "<img class='wonderplugin-carousel-media-table-image' data-order='" + i + "' src='" + wonderplugin_carousel_config.slides[i].thumbnail + "' />" + "</div>" + "<div class='wonderplugin-carousel-media-table-type'>" + mediaType[wonderplugin_carousel_config.slides[i].type] + "</div>" + "<div class='wonderplugin-carousel-media-table-buttons-edit'>" +
                "<a class='wonderplugin-carousel-media-table-button wonderplugin-carousel-media-table-edit'>Edit</a>&nbsp;|&nbsp;" + "<a class='wonderplugin-carousel-media-table-button wonderplugin-carousel-media-table-delete'>Delete</a>" + "</div>" + "<div class='wonderplugin-carousel-media-table-buttons-move'>" + "<a class='wonderplugin-carousel-media-table-button wonderplugin-carousel-media-table-moveup'>Move Up</a>&nbsp;|&nbsp;" + "<a class='wonderplugin-carousel-media-table-button wonderplugin-carousel-media-table-movedown'>Move Down</a>" +
                "</div>" + "<div style='clear:both;'></div>" + "</li>");
            $(".wonderplugin-carousel-media-table-image").wpdraggable(wonderPluginMediaTableMove)
        };
        $("#wonderplugin-add-image").click(function () {
            slideDialog(0, function (items) {
                items.map(function (data) {
                    wonderplugin_carousel_config.slides.push({type: 0, image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image, displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm, title: data.title, description: data.description, weblink: data.weblink,
                        linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight})
                });
                updateMediaTable()
            })
        });
        $("#wonderplugin-add-video").click(function () {
            slideDialog(1, function (items) {
                items.map(function (data) {
                    wonderplugin_carousel_config.slides.push({type: 1, image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image, displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm,
                        title: data.title, description: data.description, weblink: data.weblink, linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight})
                });
                updateMediaTable()
            })
        });
        $("#wonderplugin-add-youtube").click(function () {
            onlineVideoDialog(2, function (items) {
                items.map(function (data) {
                    wonderplugin_carousel_config.slides.push({type: 2, image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image,
                        displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm, title: data.title, description: data.description, weblink: data.weblink, linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight})
                });
                updateMediaTable()
            })
        });
        $("#wonderplugin-add-vimeo").click(function () {
            onlineVideoDialog(3, function (items) {
                items.map(function (data) {
                    wonderplugin_carousel_config.slides.push({type: 2,
                        image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image, displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm, title: data.title, description: data.description, weblink: data.weblink, linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight})
                });
                updateMediaTable()
            })
        });
        $(document).on("click", ".wonderplugin-carousel-media-table-edit", function () {
            var index =
                $(this).parent().parent().index();
            var mediaType = wonderplugin_carousel_config.slides[index].type;
            slideDialog(mediaType, function (items) {
                if (items && items.length > 0) {
                    wonderplugin_carousel_config.slides.splice(index, 1);
                    items.map(function (data) {
                        wonderplugin_carousel_config.slides.splice(index, 0, {type: mediaType, image: data.image, thumbnail: data.thumbnail ? data.thumbnail : data.image, displaythumbnail: data.displaythumbnail, video: data.video, mp4: data.mp4, webm: data.webm, title: data.title, description: data.description,
                            weblink: data.weblink, linktarget: data.linktarget, weblinklightbox: data.weblinklightbox, lightbox: data.lightbox, lightboxsize: data.lightboxsize, lightboxwidth: data.lightboxwidth, lightboxheight: data.lightboxheight})
                    });
                    updateMediaTable()
                }
            }, wonderplugin_carousel_config.slides[index], index)
        });
        $(document).on("click", ".wonderplugin-carousel-media-table-delete", function () {
            var $tr = $(this).parent().parent();
            var index = $tr.index();
            wonderplugin_carousel_config.slides.splice(index, 1);
            $tr.remove();
            $("#wonderplugin-carousel-media-table").find("li").each(function (index) {
                $(this).find(".wonderplugin-carousel-media-table-id").text(index +
                    1);
                $(this).find("img").data("order", index);
                $(this).find("img").css({top: 0, left: 0})
            })
        });
        var wonderPluginMediaTableMove = function (i, j) {
            var len = wonderplugin_carousel_config.slides.length;
            if (j < 0)j = 0;
            if (j > len - 1)j = len - 1;
            if (i == j) {
                $("#wonderplugin-carousel-media-table").find("li").eq(i).find("img").css({top: 0, left: 0});
                return
            }
            var $tr = $("#wonderplugin-carousel-media-table").find("li").eq(i);
            var data = wonderplugin_carousel_config.slides[i];
            wonderplugin_carousel_config.slides.splice(i, 1);
            wonderplugin_carousel_config.slides.splice(j,
                0, data);
            var $trj = $("#wonderplugin-carousel-media-table").find("li").eq(j);
            $tr.remove();
            if (j > i)$trj.after($tr); else $trj.before($tr);
            $("#wonderplugin-carousel-media-table").find("li").each(function (index) {
                $(this).find(".wonderplugin-carousel-media-table-id").text(index + 1);
                $(this).find("img").data("order", index);
                $(this).find("img").css({top: 0, left: 0})
            });
            $tr.find("img").wpdraggable(wonderPluginMediaTableMove)
        };
        $(document).on("click", ".wonderplugin-carousel-media-table-moveup", function () {
            var $tr = $(this).parent().parent();
            var index = $tr.index();
            var data = wonderplugin_carousel_config.slides[index];
            wonderplugin_carousel_config.slides.splice(index, 1);
            if (index == 0) {
                wonderplugin_carousel_config.slides.push(data);
                var $last = $tr.parent().find("li:last");
                $tr.remove();
                $last.after($tr)
            } else {
                wonderplugin_carousel_config.slides.splice(index - 1, 0, data);
                var $prev = $tr.prev();
                $tr.remove();
                $prev.before($tr)
            }
            $("#wonderplugin-carousel-media-table").find("li").each(function (index) {
                $(this).find(".wonderplugin-carousel-media-table-id").text(index +
                    1);
                $(this).find("img").data("order", index);
                $(this).find("img").css({top: 0, left: 0})
            });
            $tr.find("img").wpdraggable(wonderPluginMediaTableMove)
        });
        $(document).on("click", ".wonderplugin-carousel-media-table-movedown", function () {
            var $tr = $(this).parent().parent();
            var index = $tr.index();
            var len = wonderplugin_carousel_config.slides.length;
            var data = wonderplugin_carousel_config.slides[index];
            wonderplugin_carousel_config.slides.splice(index, 1);
            if (index == len - 1) {
                wonderplugin_carousel_config.slides.unshift(data);
                var $first = $tr.parent().find("li:first");
                $tr.remove();
                $first.before($tr)
            } else {
                wonderplugin_carousel_config.slides.splice(index + 1, 0, data);
                var $next = $tr.next();
                $tr.remove();
                $next.after($tr)
            }
            $("#wonderplugin-carousel-media-table").find("li").each(function (index) {
                $(this).find(".wonderplugin-carousel-media-table-id").text(index + 1);
                $(this).find("img").data("order", index);
                $(this).find("img").css({top: 0, left: 0})
            });
            $tr.find("img").wpdraggable(wonderPluginMediaTableMove)
        });
        var configSkinOptions = ["width", "height",
            "rownumber", "autoplay", "random", "circular", "direction", "responsive", "visibleitems", "pauseonmouseover", "scrollmode", "interval", "transitionduration", "continuous", "continuousduration", "arrowstyle", "arrowimage", "arrowwidth", "arrowheight", "navstyle", "navimage", "navwidth", "navheight", "navspacing", "showhoveroverlay", "hoveroverlayimage", "screenquery"];
        var defaultSkinOptions = {};
        for (var key in WONDERPLUGIN_CAROUSEL_SKIN_OPTIONS) {
            defaultSkinOptions[key] = {};
            for (var i = 0; i < configSkinOptions.length; i++)defaultSkinOptions[key][configSkinOptions[i]] =
                WONDERPLUGIN_CAROUSEL_SKIN_OPTIONS[key][configSkinOptions[i]];
            defaultSkinOptions[key]["skintemplate"] = WONDERPLUGIN_CAROUSEL_SKIN_TEMPLATE[key]["skintemplate"];
            defaultSkinOptions[key]["skincss"] = WONDERPLUGIN_CAROUSEL_SKIN_TEMPLATE[key]["skincss"];
            defaultSkinOptions[key]["arrowimagemode"] = "defined";
            defaultSkinOptions[key]["navimagemode"] = "defined";
            defaultSkinOptions[key]["hoveroverlayimagemode"] = "defined";
            defaultSkinOptions[key]["showhoveroverlayalways"] = false;
            defaultSkinOptions[key]["usescreenquery"] =
                false
        }
        var printSkinOptions = function (options) {
            $("#wonderplugin-carousel-width").val(options.width);
            $("#wonderplugin-carousel-height").val(options.height);
            $("#wonderplugin-carousel-rownumber").val(options.rownumber);
            $("#wonderplugin-carousel-autoplay").attr("checked", options.autoplay);
            $("#wonderplugin-carousel-random").attr("checked", options.random);
            $("#wonderplugin-carousel-circular").attr("checked", options.circular);
            $("#wonderplugin-carousel-responsive").attr("checked", options.responsive);
            $("#wonderplugin-carousel-visibleitems").val(options.visibleitems);
            $("#wonderplugin-carousel-pauseonmouseover").attr("checked", options.pauseonmouseover);
            $("#wonderplugin-carousel-scrollmode").val(options.scrollmode);
            $("#wonderplugin-carousel-interval").val(options.interval);
            $("#wonderplugin-carousel-transitionduration").val(options.transitionduration);
            $("#wonderplugin-carousel-continuous").attr("checked", options.continuous);
            $("#wonderplugin-carousel-continuousduration").val(options.continuousduration);
            if (options.usescreenquery)$("input:radio[name=wonderplugin-carousel-usescreenquery][value=screensize]").attr("checked",
                true); else $("input:radio[name=wonderplugin-carousel-usescreenquery][value=fixedsize]").attr("checked", true);
            $("#wonderplugin-carousel-screenquery").val(options.screenquery);
            $("#wonderplugin-carousel-arrowstyle").val(options.arrowstyle);
            $("input:radio[name=wonderplugin-carousel-arrowimagemode][value=" + options.arrowimagemode + "]").attr("checked", true);
            if (wonderplugin_carousel_config.arrowimagemode == "custom") {
                $("#wonderplugin-carousel-customarrowimage").val(options.arrowimage);
                $("#wonderplugin-carousel-displayarrowimage").attr("src",
                    options.arrowimage)
            } else {
                $("#wonderplugin-carousel-arrowimage").val(options.arrowimage);
                $("#wonderplugin-carousel-displayarrowimage").attr("src", $("#wonderplugin-carousel-jsfolder").text() + options.arrowimage)
            }
            $("#wonderplugin-carousel-arrowwidth").val(options.arrowwidth);
            $("#wonderplugin-carousel-arrowheight").val(options.arrowheight);
            $("#wonderplugin-carousel-showhoveroverlay").attr("checked", options.showhoveroverlay);
            $("#wonderplugin-carousel-showhoveroverlayalways").attr("checked", options.showhoveroverlayalways);
            $("input:radio[name=wonderplugin-carousel-hoveroverlayimagemode][value=" + options.hoveroverlayimagemode + "]").attr("checked", true);
            if (wonderplugin_carousel_config.hoveroverlayimagemode == "custom") {
                $("#wonderplugin-carousel-customhoveroverlayimage").val(options.hoveroverlayimage);
                $("#wonderplugin-carousel-displayhoveroverlayimage").attr("src", options.hoveroverlayimage)
            } else {
                $("#wonderplugin-carousel-hoveroverlayimage").val(options.hoveroverlayimage);
                $("#wonderplugin-carousel-displayhoveroverlayimage").attr("src",
                    $("#wonderplugin-carousel-jsfolder").text() + options.hoveroverlayimage)
            }
            $("#wonderplugin-carousel-navstyle").val(options.navstyle);
            $("input:radio[name=wonderplugin-carousel-navimagemode][value=" + options.navimagemode + "]").attr("checked", true);
            if (wonderplugin_carousel_config.navimagemode == "custom") {
                $("#wonderplugin-carousel-customnavimage").val(options.navimage);
                $("#wonderplugin-carousel-displaynavimage").attr("src", options.navimage)
            } else {
                $("#wonderplugin-carousel-navimage").val(options.navimage);
                $("#wonderplugin-carousel-displaynavimage").attr("src",
                    $("#wonderplugin-carousel-jsfolder").text() + options.navimage)
            }
            $("#wonderplugin-carousel-navwidth").val(options.navwidth);
            $("#wonderplugin-carousel-navheight").val(options.navheight);
            $("#wonderplugin-carousel-navspacing").val(options.navspacing);
            $("#wonderplugin-carousel-skintemplate").val(options.skintemplate);
            $("#wonderplugin-carousel-skincss").val(options.skincss)
        };
        $("input:radio[name=wonderplugin-carousel-skin]").click(function () {
            if ($(this).val() == wonderplugin_carousel_config.skin)return;
            $(".wonderplugin-tab-skin").find("img").removeClass("selected");
            $("input:radio[name=wonderplugin-carousel-skin]:checked").parent().find("img").addClass("selected");
            wonderplugin_carousel_config.skin = $(this).val();
            printSkinOptions(defaultSkinOptions[$(this).val()])
        });
        $(".wonderplugin-carousel-options-menu-item").each(function (index) {
            $(this).click(function () {
                if ($(this).hasClass("wonderplugin-carousel-options-menu-item-selected"))return;
                $(".wonderplugin-carousel-options-menu-item").removeClass("wonderplugin-carousel-options-menu-item-selected");
                $(this).addClass("wonderplugin-carousel-options-menu-item-selected");
                $(".wonderplugin-carousel-options-tab").removeClass("wonderplugin-carousel-options-tab-selected");
                $(".wonderplugin-carousel-options-tab").eq(index).addClass("wonderplugin-carousel-options-tab-selected")
            })
        });
        var updateCarouselOptions = function () {
            wonderplugin_carousel_config.name = $.trim($("#wonderplugin-carousel-name").val());
            wonderplugin_carousel_config.skin = $("input:radio[name=wonderplugin-carousel-skin]:checked").val();
            wonderplugin_carousel_config.width = parseInt($.trim($("#wonderplugin-carousel-width").val()));
            wonderplugin_carousel_config.height = parseInt($.trim($("#wonderplugin-carousel-height").val()));
            wonderplugin_carousel_config.rownumber = parseInt($.trim($("#wonderplugin-carousel-rownumber").val()));
            wonderplugin_carousel_config.autoplay = $("#wonderplugin-carousel-autoplay").is(":checked");
            wonderplugin_carousel_config.random = $("#wonderplugin-carousel-random").is(":checked");
            wonderplugin_carousel_config.circular = $("#wonderplugin-carousel-circular").is(":checked");
            wonderplugin_carousel_config.responsive =
                $("#wonderplugin-carousel-responsive").is(":checked");
            wonderplugin_carousel_config.visibleitems = parseInt($.trim($("#wonderplugin-carousel-visibleitems").val()));
            if (isNaN(wonderplugin_carousel_config.visibleitems) || wonderplugin_carousel_config.visibleitems < 1)wonderplugin_carousel_config.visibleitems = 3;
            wonderplugin_carousel_config.pauseonmouseover = $("#wonderplugin-carousel-pauseonmouseover").is(":checked");
            wonderplugin_carousel_config.scrollmode = $("#wonderplugin-carousel-scrollmode").val();
            wonderplugin_carousel_config.interval =
                parseInt($.trim($("#wonderplugin-carousel-interval").val()));
            wonderplugin_carousel_config.transitionduration = parseInt($.trim($("#wonderplugin-carousel-transitionduration").val()));
            wonderplugin_carousel_config.continuous = $("#wonderplugin-carousel-continuous").is(":checked");
            wonderplugin_carousel_config.continuousduration = parseInt($.trim($("#wonderplugin-carousel-continuousduration").val()));
            if ($("input[name=wonderplugin-carousel-usescreenquery]:checked").val() == "screensize")wonderplugin_carousel_config.usescreenquery =
                true; else wonderplugin_carousel_config.usescreenquery = false;
            wonderplugin_carousel_config.screenquery = $.trim($("#wonderplugin-carousel-screenquery").val());
            wonderplugin_carousel_config.arrowstyle = $("#wonderplugin-carousel-arrowstyle").val();
            wonderplugin_carousel_config.arrowimagemode = $("input[name=wonderplugin-carousel-arrowimagemode]:checked").val();
            if (wonderplugin_carousel_config.arrowimagemode == "custom")wonderplugin_carousel_config.arrowimage = $.trim($("#wonderplugin-carousel-customarrowimage").val());
            else wonderplugin_carousel_config.arrowimage = $.trim($("#wonderplugin-carousel-arrowimage").val());
            wonderplugin_carousel_config.arrowwidth = parseInt($.trim($("#wonderplugin-carousel-arrowwidth").val()));
            if (isNaN(wonderplugin_carousel_config.arrowwidth) || wonderplugin_carousel_config.arrowwidth < 0)wonderplugin_carousel_config.arrowwidth = defaultSkinOptions[wonderplugin_carousel_config.skin]["arrowwidth"];
            wonderplugin_carousel_config.arrowheight = parseInt($.trim($("#wonderplugin-carousel-arrowheight").val()));
            if (isNaN(wonderplugin_carousel_config.arrowheight) || wonderplugin_carousel_config.arrowheight < 0)wonderplugin_carousel_config.arrowheight = defaultSkinOptions[wonderplugin_carousel_config.skin]["arrowheight"];
            wonderplugin_carousel_config.showhoveroverlay = $("#wonderplugin-carousel-showhoveroverlay").is(":checked");
            wonderplugin_carousel_config.showhoveroverlayalways = $("#wonderplugin-carousel-showhoveroverlayalways").is(":checked");
            wonderplugin_carousel_config.hoveroverlayimagemode = $("input[name=wonderplugin-carousel-hoveroverlayimagemode]:checked").val();
            if (wonderplugin_carousel_config.hoveroverlayimagemode == "custom")wonderplugin_carousel_config.hoveroverlayimage = $.trim($("#wonderplugin-carousel-customhoveroverlayimage").val()); else wonderplugin_carousel_config.hoveroverlayimage = $.trim($("#wonderplugin-carousel-hoveroverlayimage").val());
            wonderplugin_carousel_config.navstyle = $("#wonderplugin-carousel-navstyle").val();
            wonderplugin_carousel_config.navimagemode = $("input[name=wonderplugin-carousel-navimagemode]:checked").val();
            if (wonderplugin_carousel_config.navimagemode ==
                "custom")wonderplugin_carousel_config.navimage = $.trim($("#wonderplugin-carousel-customnavimage").val()); else wonderplugin_carousel_config.navimage = $.trim($("#wonderplugin-carousel-navimage").val());
            wonderplugin_carousel_config.navwidth = parseInt($.trim($("#wonderplugin-carousel-navwidth").val()));
            if (isNaN(wonderplugin_carousel_config.navwidth) || wonderplugin_carousel_config.navwidth < 0)wonderplugin_carousel_config.navwidth = defaultSkinOptions[wonderplugin_carousel_config.skin]["navwidth"];
            wonderplugin_carousel_config.navheight =
                parseInt($.trim($("#wonderplugin-carousel-navheight").val()));
            if (isNaN(wonderplugin_carousel_config.navheight) || wonderplugin_carousel_config.navheight < 0)wonderplugin_carousel_config.navheight = defaultSkinOptions[wonderplugin_carousel_config.skin]["navheight"];
            wonderplugin_carousel_config.navspacing = parseInt($.trim($("#wonderplugin-carousel-navspacing").val()));
            if (isNaN(wonderplugin_carousel_config.navspacing))wonderplugin_carousel_config.navspacing = defaultSkinOptions[wonderplugin_carousel_config.skin]["navspacing"];
            wonderplugin_carousel_config.direction = defaultSkinOptions[wonderplugin_carousel_config.skin]["direction"];
            wonderplugin_carousel_config.skintemplate = $.trim($("#wonderplugin-carousel-skintemplate").val()).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
            wonderplugin_carousel_config.skincss = $.trim($("#wonderplugin-carousel-skincss").val());
            wonderplugin_carousel_config.customcss = $.trim($("#wonderplugin-carousel-custom-css").val());
            wonderplugin_carousel_config.dataoptions = $.trim($("#wonderplugin-carousel-data-options").val());
            wonderplugin_carousel_config.lightboxresponsive = $("#wonderplugin-carousel-lightboxresponsive").is(":checked");
            wonderplugin_carousel_config.lightboxshownavigation = $("#wonderplugin-carousel-lightboxshownavigation").is(":checked");
            wonderplugin_carousel_config.lightboxnogroup = $("#wonderplugin-carousel-lightboxnogroup").is(":checked");
            wonderplugin_carousel_config.lightboxshowtitle = $("#wonderplugin-carousel-lightboxshowtitle").is(":checked");
            wonderplugin_carousel_config.lightboxshowdescription = $("#wonderplugin-carousel-lightboxshowdescription").is(":checked");
            wonderplugin_carousel_config.donotinit = $("#wonderplugin-carousel-donotinit").is(":checked");
            wonderplugin_carousel_config.addinitscript = $("#wonderplugin-carousel-addinitscript").is(":checked");
            var val = parseInt($.trim($("#wonderplugin-carousel-lightboxthumbwidth").val()));
            wonderplugin_carousel_config.lightboxthumbwidth = isNaN(val) ? default_options.lightboxthumbwidth : val;
            val = parseInt($.trim($("#wonderplugin-carousel-lightboxthumbheight").val()));
            wonderplugin_carousel_config.lightboxthumbheight = isNaN(val) ?
                default_options.lightboxthumbheight : val;
            val = parseInt($.trim($("#wonderplugin-carousel-lightboxthumbtopmargin").val()));
            wonderplugin_carousel_config.lightboxthumbtopmargin = isNaN(val) ? default_options.lightboxthumbtopmargin : val;
            val = parseInt($.trim($("#wonderplugin-carousel-lightboxthumbbottommargin").val()));
            wonderplugin_carousel_config.lightboxthumbbottommargin = isNaN(val) ? default_options.lightboxthumbbottommargin : val;
            val = parseInt($.trim($("#wonderplugin-carousel-lightboxbarheight").val()));
            wonderplugin_carousel_config.lightboxbarheight =
                isNaN(val) ? default_options.lightboxbarheight : val;
            wonderplugin_carousel_config.lightboxtitlebottomcss = $.trim($("#wonderplugin-carousel-lightboxtitlebottomcss").val());
            wonderplugin_carousel_config.lightboxdescriptionbottomcss = $.trim($("#wonderplugin-carousel-lightboxdescriptionbottomcss").val())
        };
        var previewCarousel = function () {
            updateCarouselOptions();
            $("#wonderplugin-carousel-preview-container").empty();
            var previewCode = "<div id='wonderplugin-carousel-preview'";
            if (wonderplugin_carousel_config.dataoptions &&
                wonderplugin_carousel_config.dataoptions.length > 0)previewCode += " " + wonderplugin_carousel_config.dataoptions;
            previewCode += "></div>";
            $("#wonderplugin-carousel-preview-container").html(previewCode);
            if (wonderplugin_carousel_config.slides.length > 0) {
                $("head").find("style").each(function () {
                    if ($(this).data("creator") == "wonderplugincarouselcreator")$(this).remove()
                });
                var carouselid = wonderplugin_carousel_config.id > 0 ? wonderplugin_carousel_config.id : 0;
                if (wonderplugin_carousel_config.customcss && wonderplugin_carousel_config.customcss.length >
                    0) {
                    var customcss = wonderplugin_carousel_config.customcss.replace(/CAROUSELID/g, carouselid);
                    $("head").append("<style type='text/css' data-creator='wonderplugincarouselcreator'>" + customcss + "</style>")
                }
                if (wonderplugin_carousel_config.skincss && wonderplugin_carousel_config.skincss.length > 0)$("head").append("<style type='text/css' data-creator='wonderplugincarouselcreator'>" + wonderplugin_carousel_config.skincss.replace(/#amazingcarousel-CAROUSELID/g, "#wonderplugin-carousel-preview") + "</style>");
                var i;
                var code =
                    '<div class="amazingcarousel-list-container" style="overflow:hidden;">';
                code += '<ul class="amazingcarousel-list">';
                if (isNaN(wonderplugin_carousel_config.rownumber) || wonderplugin_carousel_config.rownumber < 1)wonderplugin_carousel_config.rownumber = 1;
                for (i = 0; i < wonderplugin_carousel_config.slides.length; i++) {
                    if (i == 0)code += '<li class="amazingcarousel-item">'; else if (i % wonderplugin_carousel_config.rownumber == 0)code += '</li><li class="amazingcarousel-item">';
                    code += '<div class="amazingcarousel-item-container">';
                    var image_code = "";
                    if (wonderplugin_carousel_config.slides[i].lightbox) {
                        image_code += '<a href="';
                        if (wonderplugin_carousel_config.slides[i].type == 0)image_code += wonderplugin_carousel_config.slides[i].image; else if (wonderplugin_carousel_config.slides[i].type == 1) {
                            image_code += wonderplugin_carousel_config.slides[i].mp4;
                            if (wonderplugin_carousel_config.slides[i].webm)image_code += '" data-webm="' + wonderplugin_carousel_config.slides[i].webm
                        } else if (wonderplugin_carousel_config.slides[i].type == 2 || wonderplugin_carousel_config.slides[i].type ==
                            3)image_code += wonderplugin_carousel_config.slides[i].video;
                        if (wonderplugin_carousel_config.slides[i].title && wonderplugin_carousel_config.slides[i].title.length > 0)image_code += '" title="' + wonderplugin_carousel_config.slides[i].title.replace(/"/g, "&quot;");
                        if (wonderplugin_carousel_config.slides[i].description && wonderplugin_carousel_config.slides[i].description.length > 0)image_code += '" data-description="' + wonderplugin_carousel_config.slides[i].description.replace(/"/g, "&quot;");
                        if (wonderplugin_carousel_config.slides[i].lightboxsize)image_code +=
                            '" data-width="' + wonderplugin_carousel_config.slides[i].lightboxwidth + '" data-height="' + wonderplugin_carousel_config.slides[i].lightboxheight;
                        image_code += '" data-thumbnail="' + wonderplugin_carousel_config.slides[i].thumbnail;
                        image_code += '" class="wondercarousellightbox wondercarousellightbox-' + carouselid + '"';
                        if (!wonderplugin_carousel_config.lightboxnogroup)image_code += ' data-group="wondercarousellightbox-' + carouselid + '"';
                        image_code += ">"
                    } else if (wonderplugin_carousel_config.slides[i].weblink && wonderplugin_carousel_config.slides[i].weblink.length >
                        0) {
                        image_code += '<a href="' + wonderplugin_carousel_config.slides[i].weblink + '"';
                        if (wonderplugin_carousel_config.slides[i].linktarget && wonderplugin_carousel_config.slides[i].linktarget.length > 0)image_code += ' target="' + wonderplugin_carousel_config.slides[i].linktarget + '"';
                        if (wonderplugin_carousel_config.slides[i].weblinklightbox) {
                            image_code += '" class="wondercarousellightbox wondercarousellightbox-' + carouselid + '"';
                            if (!wonderplugin_carousel_config.lightboxnogroup)image_code += ' data-group="wondercarousellightbox-' +
                                carouselid + '"';
                            if (wonderplugin_carousel_config.slides[i].lightboxsize)image_code += ' data-width="' + wonderplugin_carousel_config.slides[i].lightboxwidth + '" data-height="' + wonderplugin_carousel_config.slides[i].lightboxheight + '"'
                        }
                        image_code += ">"
                    }
                    if (wonderplugin_carousel_config.slides[i].displaythumbnail)image_code += '<img src="' + wonderplugin_carousel_config.slides[i].thumbnail + '"'; else image_code += '<img src="' + wonderplugin_carousel_config.slides[i].image + '"';
                    image_code += ' alt="' + wonderplugin_carousel_config.slides[i].title.replace(/"/g,
                        "&quot;") + '"';
                    image_code += ' data-description="' + wonderplugin_carousel_config.slides[i].description.replace(/"/g, "&quot;") + '"';
                    if (!wonderplugin_carousel_config.slides[i].lightbox)if (wonderplugin_carousel_config.slides[i].type == 1) {
                        image_code += ' data-video="' + wonderplugin_carousel_config.slides[i].mp4 + '"';
                        if (wonderplugin_carousel_config.slides[i].webm)image_code += ' data-videowebm="' + wonderplugin_carousel_config.slides[i].webm + '"'
                    } else if (wonderplugin_carousel_config.slides[i].type == 2 || wonderplugin_carousel_config.slides[i].type ==
                        3)image_code += ' data-video="' + wonderplugin_carousel_config.slides[i].video + '"';
                    image_code += " />";
                    if (wonderplugin_carousel_config.slides[i].lightbox || !wonderplugin_carousel_config.slides[i].lightbox && wonderplugin_carousel_config.slides[i].weblink && wonderplugin_carousel_config.slides[i].weblink.length > 0)image_code += "</a>";
                    var title_code = "";
                    if (wonderplugin_carousel_config.slides[i].title && wonderplugin_carousel_config.slides[i].title.length > 0)title_code = wonderplugin_carousel_config.slides[i].title;
                    var description_code =
                        "";
                    if (wonderplugin_carousel_config.slides[i].description && wonderplugin_carousel_config.slides[i].description.length > 0)description_code = wonderplugin_carousel_config.slides[i].description;
                    var skin_template = wonderplugin_carousel_config.skintemplate.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/__IMAGE__/g, image_code).replace(/__TITLE__/g, title_code).replace(/__DESCRIPTION__/g, description_code);
                    if (wonderplugin_carousel_config.slides[i].weblink && wonderplugin_carousel_config.slides[i].weblink.length >
                        0) {
                        skin_template = skin_template.replace(/__HREF__/g, wonderplugin_carousel_config.slides[i].weblink);
                        if (wonderplugin_carousel_config.slides[i].linktarget)skin_template = skin_template.replace(/__TARGET__/g, wonderplugin_carousel_config.slides[i].linktarget)
                    }
                    code += skin_template;
                    code += "</div>"
                }
                if (wonderplugin_carousel_config.slides.length > 0)code += "</li>";
                code += "</ul>";
                code += '<div class="amazingcarousel-prev"></div><div class="amazingcarousel-next"></div>';
                code += "</div>";
                code += '<div class="amazingcarousel-nav"></div>';
                var jsfolder = $("#wonderplugin-carousel-jsfolder").text();
                var carouselOptions = $.extend({}, WONDERPLUGIN_CAROUSEL_SKIN_OPTIONS[wonderplugin_carousel_config["skin"]], {carouselid: carouselid, jsfolder: jsfolder}, wonderplugin_carousel_config);
                var totalwidth;
                if (carouselOptions.direction == "vertical")totalwidth = carouselOptions.width; else totalwidth = carouselOptions.width * carouselOptions.visibleitems;
                if (carouselOptions.responsive)$("#wonderplugin-carousel-preview").css({display: "none", position: "relative", width: "100%",
                    "max-width": totalwidth + "px"}); else $("#wonderplugin-carousel-preview").css({display: "none", position: "relative", width: totalwidth + "px"});
                $("#wonderplugin-carousel-preview").html(code);
                carouselOptions.screenquery = jQuery.parseJSON(carouselOptions.screenquery);
                $("#wonderplugin-carousel-preview").wonderplugincarouselslider(carouselOptions)
            }
        };
        var postPublish = function (message) {
            $("#wonderplugin-carousel-publish-loading").hide();
            var formnonce = $("#wonderplugin-carousel-saveformnonce").html();
            var errorHtml = "";
            if (message) {
                errorHtml += "<div class='error'><p>Error message: " + message + "</p></div>";
                errorHtml += "<div class='error'><p>WordPress Ajax call failed. Please click the button below and save the tab group with POST method</p></div>"
            } else {
                errorHtml += "<div class='error'><p>WordPress Ajax call failed. Please check your WordPress configuration file and make sure the WP_DEBUG is set to false.</p></div>";
                errorHtml += "<div class='error'><p>Please click the button below and save the tab group with POST method</p></div>"
            }
            errorHtml +=
                "<form method='post'>";
            errorHtml += formnonce;
            errorHtml += "<input type='hidden' name='wonderplugin-carousel-save-item-post-value' id='wonderplugin-carousel-save-item-post-value' value='" + JSON.stringify(wonderplugin_carousel_config).replace(/"/g, "&quot;").replace(/'/g, "&#39;") + "' />";
            errorHtml += "<p class='submit'><input type='submit' name='wonderplugin-carousel-save-item-post' id='wonderplugin-carousel-save-item-post' class='button button-primary' value='Save & Publish with Post Method'  /></p>";
            errorHtml +=
                "</form>";
            $("#wonderplugin-carousel-publish-information").html(errorHtml)
        };
        var publishCarousel = function () {
            $("#wonderplugin-carousel-publish-loading").show();
            updateCarouselOptions();
            var ajaxnonce = $("#wonderplugin-carousel-ajaxnonce").text();
            jQuery.ajax({url: ajaxurl, type: "POST", data: {action: "wonderplugin_carousel_save_config", nonce: ajaxnonce, item: JSON.stringify(wonderplugin_carousel_config)}, success: function (data) {
                $("#wonderplugin-carousel-publish-loading").hide();
                if (data && data.success && data.id >= 0) {
                    wonderplugin_carousel_config.id =
                        data.id;
                    $("#wonderplugin-carousel-publish-information").html("<div class='updated'><p>The carousel has been saved and published.</p></div>" + "<div class='updated'><p>To embed the carousel into your page or post, use shortcode:  [wonderplugin_carousel id=\"" + data.id + '"]</p></div>' + "<div class='updated'><p>To embed the carousel into your template, use php code:  &lt;?php echo do_shortcode('[wonderplugin_carousel id=\"" + data.id + "\"]'); ?&gt;</p></div>")
                } else postPublish(data && data.message ? data.message :
                    "")
            }, error: function () {
                $("#wonderplugin-carousel-publish-loading").hide();
                postPublish("")
            }})
        };
        var default_options = {id: -1, name: "My Carousel", slides: [], skin: "classic", customcss: "", dataoptions: "", lightboxresponsive: true, lightboxshownavigation: false, lightboxnogroup: false, lightboxshowtitle: true, lightboxshowdescription: false, donotinit: false, addinitscript: false, lightboxthumbwidth: 90, lightboxthumbheight: 60, lightboxthumbtopmargin: 12, lightboxthumbbottommargin: 4, lightboxbarheight: 64, lightboxtitlebottomcss: "{color:#333; font-size:14px; font-family:Armata,sans-serif,Arial; overflow:hidden; text-align:left;}",
            lightboxdescriptionbottomcss: "{color:#333; font-size:12px; font-family:Arial,Helvetica,sans-serif; overflow:hidden; text-align:left; margin:4px 0px 0px; padding: 0px;}"};
        var wonderplugin_carousel_config = $.extend({}, default_options, defaultSkinOptions[default_options["skin"]]);
        var carouselId = parseInt($("#wonderplugin-carousel-id").text());
        if (carouselId >= 0) {
            $.extend(wonderplugin_carousel_config, $.parseJSON($("#wonderplugin-carousel-id-config").text()));
            wonderplugin_carousel_config.id = carouselId
        }
        var i;
        var j;
        for (i = 0; i < wonderplugin_carousel_config.slides.length; i++) {
            if (!("lightboxsize"in wonderplugin_carousel_config.slides[i]))wonderplugin_carousel_config.slides[i]["lightboxsize"] = false;
            if (!("lightboxwidth"in wonderplugin_carousel_config.slides[i]))wonderplugin_carousel_config.slides[i]["lightboxwidth"] = 640;
            if (!("lightboxheight"in wonderplugin_carousel_config.slides[i]))wonderplugin_carousel_config.slides[i]["lightboxheight"] = 480
        }
        var slideBoolOptions = ["lightbox", "lightboxsize", "displaythumbnail",
            "weblinklightbox"];
        for (i = 0; i < wonderplugin_carousel_config.slides.length; i++)for (j = 0; j < slideBoolOptions.length; j++)if (wonderplugin_carousel_config.slides[i][slideBoolOptions[j]] !== true && wonderplugin_carousel_config.slides[i][slideBoolOptions[j]] !== false)wonderplugin_carousel_config.slides[i][slideBoolOptions[j]] = wonderplugin_carousel_config.slides[i][slideBoolOptions[j]] && wonderplugin_carousel_config.slides[i][slideBoolOptions[j]].toLowerCase() === "true";
        var boolOptions = ["autoplay", "random", "circular",
            "pauseonmouseover", "continuous", "responsive", "showhoveroverlay", "showhoveroverlayalways", "lightboxresponsive", "lightboxshownavigation", "lightboxnogroup", "lightboxshowtitle", "lightboxshowdescription", "usescreenquery", "donotinit", "addinitscript"];
        for (i = 0; i < boolOptions.length; i++)if (wonderplugin_carousel_config[boolOptions[i]] !== true && wonderplugin_carousel_config[boolOptions[i]] !== false)wonderplugin_carousel_config[boolOptions[i]] = wonderplugin_carousel_config[boolOptions[i]] && wonderplugin_carousel_config[boolOptions[i]].toLowerCase() ===
            "true";
        if (wonderplugin_carousel_config.dataoptions && wonderplugin_carousel_config.dataoptions.length > 0)wonderplugin_carousel_config.dataoptions = wonderplugin_carousel_config.dataoptions.replace(/\\"/g, '"').replace(/\\'/g, "'");
        var printConfig = function () {
            $("#wonderplugin-carousel-name").val(wonderplugin_carousel_config.name);
            updateMediaTable();
            $(".wonderplugin-tab-skin").find("img").removeClass("selected");
            $("input:radio[name=wonderplugin-carousel-skin][value=" + wonderplugin_carousel_config.skin + "]").attr("checked",
                true);
            $("input:radio[name=wonderplugin-carousel-skin][value=" + wonderplugin_carousel_config.skin + "]").parent().find("img").addClass("selected");
            printSkinOptions(wonderplugin_carousel_config);
            $("#wonderplugin-carousel-custom-css").val(wonderplugin_carousel_config.customcss);
            $("#wonderplugin-carousel-data-options").val(wonderplugin_carousel_config.dataoptions);
            $("#wonderplugin-carousel-lightboxresponsive").attr("checked", wonderplugin_carousel_config.lightboxresponsive);
            $("#wonderplugin-carousel-lightboxshownavigation").attr("checked",
                wonderplugin_carousel_config.lightboxshownavigation);
            $("#wonderplugin-carousel-lightboxnogroup").attr("checked", wonderplugin_carousel_config.lightboxnogroup);
            $("#wonderplugin-carousel-lightboxshowtitle").attr("checked", wonderplugin_carousel_config.lightboxshowtitle);
            $("#wonderplugin-carousel-lightboxshowdescription").attr("checked", wonderplugin_carousel_config.lightboxshowdescription);
            $("#wonderplugin-carousel-lightboxthumbwidth").val(wonderplugin_carousel_config.lightboxthumbwidth);
            $("#wonderplugin-carousel-lightboxthumbheight").val(wonderplugin_carousel_config.lightboxthumbheight);
            $("#wonderplugin-carousel-lightboxthumbtopmargin").val(wonderplugin_carousel_config.lightboxthumbtopmargin);
            $("#wonderplugin-carousel-lightboxthumbbottommargin").val(wonderplugin_carousel_config.lightboxthumbbottommargin);
            $("#wonderplugin-carousel-lightboxbarheight").val(wonderplugin_carousel_config.lightboxbarheight);
            $("#wonderplugin-carousel-lightboxtitlebottomcss").val(wonderplugin_carousel_config.lightboxtitlebottomcss);
            $("#wonderplugin-carousel-lightboxdescriptionbottomcss").val(wonderplugin_carousel_config.lightboxdescriptionbottomcss);
            $("#wonderplugin-carousel-donotinit").attr("checked", wonderplugin_carousel_config.donotinit);
            $("#wonderplugin-carousel-addinitscript").attr("checked", wonderplugin_carousel_config.addinitscript)
        };
        printConfig()
    });
    $.fn.wpdraggable = function (callback) {
        this.css("cursor", "move").on("mousedown", function (e) {
            var $dragged = $(this);
            var x = $dragged.offset().left - e.pageX;
            var y = $dragged.offset().top - e.pageY;
            var z = $dragged.css("z-index");
            $(document).on("mousemove.wpdraggable",function (e) {
                $dragged.css({"z-index": 999}).offset({left: x +
                    e.pageX, top: y + e.pageY});
                e.preventDefault()
            }).one("mouseup", function () {
                    $(this).off("mousemove.wpdraggable click.wpdraggable");
                    $dragged.css("z-index", z);
                    var i = $dragged.data("order");
                    var coltotal = Math.floor($dragged.parent().parent().parent().innerWidth() / $dragged.parent().parent().outerWidth());
                    var row = Math.floor(($dragged.offset().top - $dragged.parent().parent().parent().offset().top) / $dragged.parent().parent().outerHeight());
                    var col = Math.floor(($dragged.offset().left - $dragged.parent().parent().parent().offset().left) /
                        $dragged.parent().parent().outerWidth());
                    var j = row * coltotal + col;
                    callback(i, j)
                });
            e.preventDefault()
        });
        return this
    }
})(jQuery);

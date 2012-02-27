var general = {
	trim: function(str) {
		return str.replace(/^\s+|\s+$/g, '');
	},
	
	errorMessage: function(title, message, location) {
		var dialog = new Dialog(Dialog.DIALOG_POP).showMessage(title, message);
		if(location != null) {
			dialog.onconfirm = function() { document.setLocation('http://apps.facebook.com/'+appName+'/'+location); };
		}
	},
	
	rollover: function (element, type) {
		if(type == 'over') 
			element.setStyle('borderColor', '#3B5998');
		else if(type == 'out')
			element.setStyle('borderColor', '#ccc');
	},
	
	go: function(location) {
		document.setLocation('/'+appPath+'/'+location);
	}
}

var build = {
	go: function(retry) {
		ajax = new Ajax();
		ajax.responseType = Ajax.JSON;
		ajax.requireLogin = true;
		ajax.post('http://'+hostName+'/import/build', {'ajax': true});
		ajax.ondone = function(data) {
			//var progressWidth = document.getElementById('progressbar').getClientWidth();
			//var newWidth = progressWidth * (data.percent / 100);
			//Animation(document.getElementById('progressinterior')).to('width', newWidth).go();

			if(data.status == 'completed') {
//				document.getElementById('progressinterior').setTextValue('100%').setStyle({color: '#ffffff'});
				document.setLocation('http://apps.facebook.com/'+appPath+'/import/review');
			}
			else if(data.status == 'in progress') {
//				document.getElementById('progressinterior').setTextValue(data.percent+'%').setStyle({color: '#ffffff'});
				build.go(false);
			}
			else 
				if(retry === true)  {
					general.errorMessage(data.status);
				}
				else {
					build.go(true);
				}
		};
		ajax.onerror = function() {
			if(retry === true)  {
				general.errorMessage('Error While Caching', 'A server error was encountered while caching the needed data. You will now be forwarded to the review page but it may timeout due to not having the correct data cached.', 'import/review');
			}
			else {
				build.go(true);
			}
		};
	}
};

var view = {
		check_photo_number: 0,
		
		check_all: function (action) {
			for (i = 0; document.getElementById('check_'+i); i++) {
				document.getElementById('check_'+i).setChecked(action);
			}
		},
		
		check: function (i, photo_id) {
			if(document.getElementById('check_'+i).getChecked() === true) { document.getElementById('check_'+i).setChecked(false); }
			else { document.getElementById('check_'+i).setChecked(true); view.cache(photo_id); }
		},

		cache: function (photo_id) {
			var ajax = new Ajax();
			ajax.responseType = Ajax.RAW;
			ajax.post('http://'+hostName+'/import/cache/'+photo_id);
		},

		view_full: function (photo_url, photo_number) {
			view.check_photo_number = photo_number;
			document.getElementById('display_photo').setSrc(photo_url);
			document.getElementById('view_photo').setStyle('display', 'block');
			document.getElementById('view_all').setStyle('display', 'none');
		},

		close_full: function (set, photo_number) {
			if(set === true) { document.getElementById('check_' + view.check_photo_number).setChecked(true); }
			document.getElementById('display_photo').setSrc('http://'+hostName+'/img/loading.gif');
			document.getElementById('view_photo').setStyle('display', 'none');
			document.getElementById('view_all').setStyle('display', 'block');
		},

		paging: function (page_number) {
			document.getElementById('page_number').setValue(page_number);
			document.getElementById('photos').submit();
			return false;
		}
}

var photosets = {
	quickimport: function (id, count) {
		dialogBox = true;
		if(count > 0) {
			var dialog = new Dialog(Dialog.DIALOG_POP).showChoice('QuickImport', 'QuickImport is an easy way to import a whole photoset into Facebook. Just a warning that it will remove all photos from your current queue! Are you sure you want to do this?', 'Yep', 'Nevermind');
			dialog.onconfirm = function() {
				document.setLocation('http://apps.facebook.com/'+appPath+'/view/submit/'+id);
			};
			dialog.oncancel = function() {
				dialogBox = false;
			};
		}
		else {
			document.setLocation('http://apps.facebook.com/'+appPath+'/view/submit/'+id);
		}
	}
}

var options = {
	authorizeUpload: function(obj) {
		obj.setDisabled(true);
		if(obj.getChecked() === true) {
			Facebook.showPermissionDialog('publish_stream', function(auth) {
				obj.setDisabled(false);
				if(!auth) {
					obj.setChecked(false);
				}
			});
		} else {
			var ajax = new Ajax();
			ajax.responseType = Ajax.JSON;
			ajax.requireLogin = true;
			ajax.post('http://'+hostName+'/options', { 'revoke':true });
			ajax.ondone = function() {
				obj.setDisabled(false);
			}
		}
	},
	
	clearQueue: function() {
		var dialog = new Dialog(Dialog.DIALOG_POP).showChoice('Clear Queue', 'Are you sure you want to do this?');
		dialog.onconfirm = function() { 
			var ajax = new Ajax();
			ajax.responseType = Ajax.JSON;
			var query = { 'queue':true };
			ajax.post('http://'+hostName+'/options', query);
			ajax.ondone = function(data) {
				if(data.status == 'completed')
					document.setLocation('http://apps.facebook.com/'+appPath+'/');
			}
		};
	},
	
	uninstall: function() {
		var dialog = new Dialog(Dialog.DIALOG_POP).showChoice('Uninstall Application', 'This is the last chance "Are you sure?" box.');
		dialog.onconfirm = function() {
			document.getElementById('uninstall').submit();
			return true;
		};
	},
	
	deauthorize: function() {
		var dialog = new Dialog(Dialog.DIALOG_POP).showChoice('Deauthorize', 'Are you sure you want to remove the association between FlickrImportr and the Flickr account?');
		dialog.onconfirm = function() {
			document.getElementById('deauthorize').submit();
			return true;
		};
	}
}

var review = {
		open: null,
		
		openOptions: function(id) {
			open = id;
			document.getElementById('options_'+open).setStyle('display', 'block');
			if(open != 'all')
				document.getElementById('photo_'+open).setStyle('z-index', '95');
			document.getElementById('overlay').setStyle({display: 'block', height: document.getElementById('photos').getOffsetHeight() + "px", width: '100%'});
		},

		setDescription: function (id, type, selected) {
			var obj = document.getElementById('desc_'+id);
			if(selected === true) {
				switch(type) {
				case "tags":
					obj.setValue(general.trim(obj.getValue() + '\n\nTags: ' + queue[id].tags));
					document.getElementById('tags_'+id).setChecked(true);
					break;
				case "title":
					obj.setValue(general.trim(obj.getValue() + '\n\n' + queue[id].title));
					document.getElementById('title_'+id).setChecked(true);
					break;
				case "url":
					obj.setValue(general.trim(obj.getValue() + '\n\n' + queue[id].flickr_url));
					document.getElementById('url_'+id).setChecked(true);
					break;
				case "date":
					obj.setValue(general.trim(obj.getValue() + '\n\n' + queue[id].date_taken));
					document.getElementById('date_'+id).setChecked(true);
					break;
				}
			}
			else if(selected === false) {
				switch(type) {
				case "tags":
					obj.setValue(general.trim(obj.getValue().replace("Tags: " + queue[id].tags, "")));
					document.getElementById('tags_'+id).setChecked(false);
					break;
				case "title":
					obj.setValue(general.trim(obj.getValue().replace(queue[id].title, "")));
					document.getElementById('title_'+id).setChecked(false);
					break;
				case "url":
					obj.setValue(general.trim(obj.getValue().replace(queue[id].flickr_url, "")));
					document.getElementById('url_'+id).setChecked(false);
					break;
				case "date":
					obj.setValue(general.trim(obj.getValue().replace(queue[id].date_taken, "")));
					document.getElementById('date_'+id).setChecked(false);
					break;
				}
			}
		},
		
		description: function (id, type, selected) {
			if(id == 'all') {
				for ( key in queue ) {
					if(document.getElementById('photo_'+queue[key].id) != null)
						review.setDescription(queue[key].id, type, selected);
				}
			}
			else { review.setDescription(id, type, selected); }
		},
		
		closeOptions: function() {
			document.getElementById('options_'+open).setStyle('display', 'none');
			if(open != 'all')
				document.getElementById('photo_'+open).setStyle('z-index', '1');
			document.getElementById('overlay').setStyle({display: 'none'});
			open = 0;
		},
		
		set_album_id: function(input, type) {
			switch(type) {
				case "flickr":
					document.getElementById('album_id').setValue(flickr[input].id);
					document.getElementById('album_name').setValue(flickr[input].title);
					document.getElementById('album_desc').setValue(flickr[input].description);
				break;
				case "facebook":
					document.getElementById('album_id').setValue(input);
				break;
                case "page":
                    document.getElementById('album_id').setValue(input);
                    break;
                default:
                    document.getElementById('album_id').setValue('');
			}
		},

        setTypeImport: function(type) {
            switch(type) {
                case "personal":
                    document.getElementById('page_selector').setStyle('display', 'none');
                    document.getElementById('page_albums').setStyle('display', 'none');
                    document.getElementById('personal_albums').setStyle('display', '');
                    document.getElementById('user_id').setValue('');
                break;

                case "page":
                    document.getElementById('page_selector').setStyle('display', '');
                    document.getElementById('page_albums').setStyle('display', '');
                    document.getElementById('personal_albums').setStyle('display', 'none');
                    review.changePageAlbum(document.getElementById('page_selector').getValue());
                break;
            }
        },

        changePageAlbum: function(page_id) {
            child = document.getElementById('page_albums').getChildNodes();

            for(i=0;i<child.length;i++) {
                child[i].setStyle('display', 'none');
            }
            document.getElementById('page_album_'+page_id).setStyle('display', '');
           console.log(page_id);
            document.getElementById('user_id').setValue(page_id);
        },
		
		change_import_type: function(type) {
			switch(type) {
				case "new":
                    document.getElementById('album_id').setValue('');
					document.getElementById('facebook_albums').setStyle('display', 'none');
					document.getElementById('flickr_photosets').setStyle('display', 'none');
					document.getElementById('album_row').setStyle('display', '');
					document.getElementById('desc_row').setStyle('display', '');
					break;
				case "flickr":
                    document.getElementById('album_id').setValue('');
					document.getElementById('facebook_albums').setStyle('display', 'none');
					document.getElementById('flickr_photosets').setStyle('display', '');
					document.getElementById('album_row').setStyle('display', '');
					document.getElementById('desc_row').setStyle('display', '');
					break;
				case "facebook":
                    document.getElementById('album_id').setValue('');
					document.getElementById('album_name').setValue('');
					document.getElementById('album_desc').setValue('');
					document.getElementById('facebook_albums').setStyle('display', '');
					document.getElementById('flickr_photosets').setStyle('display', 'none');
					document.getElementById('album_row').setStyle('display', 'none');
					document.getElementById('desc_row').setStyle('display', 'none');
					break;
			}
		},
		
		checkForm: function(form) {
			var params=form.serialize();
			console.dir(params);
			if(params.album_from == "facebook" && params.album_id == '') {
                general.errorMessage('Album Not Selected', 'You indicated you want import into an existing Facebook album, but you have not specified a valid album.', null);
                return false;
            }

           if(params.album_name.length == 0 && params.album_from != "facebook") {
                general.errorMessage('Album Name Required', 'The album name is required. Please fill in the album name and submit again.', null);
				return false;
			}

            return true;
            
		},
		
		remove_photo: function(photo_id, element) {
			var ajax = new Ajax();
			ajax.responseType = Ajax.JSON;
			ajax.requireLogin = true;
			var dialog = new Dialog(Dialog.DIALOG_CONTEXTUAL).setContext(element).showChoice('Remove From Queue', 'Are you sure you want to remove this photo from your queue?', 'Yep', 'Nevermind');
			dialog.onconfirm = function() {
				Animation(document.getElementById('photo_'+photo_id)).to('opacity', '.4').duration(500).go();
				var query = { "action" : "remove_photo", "photo_id" : photo_id };
				ajax.post('http://'+hostName+'/import/review', query);
				ajax.ondone = function(data) {
					if(data.status == 'completed') 
						Animation(document.getElementById('photo_'+photo_id)).to('width', '0px').to('opacity', 0).hide().ease(Animation.ease.end).go();
					else {
						document.getElementById('photo_'+photo_id).setStyle('opacity', '1');
						errorMessage('Error Removing Photo', 'There was an error in removing the photo. The photo was most likely already removed from your queue.');
					}
				};
				ajax.onerror = function() {
					document.getElementById('photo_'+photo_id).setStyle('opacity', '1');
					errorMessage('Error', 'There was an error in removing the photo. Please try again.');
				}
			}; return false;
		}
	}

var jobs = {
		active_jobs: { },
		report: function(jobid,object) {
			var dialog_input = new Dialog(Dialog.DIALOG_CONTEXTUAL).setContext(object);
			var dialog_confirm = new Dialog(Dialog.DIALOG_CONTEXTUAL).setContext(object);
			dialog_input.showChoice('Report Issue', bug_report, 'Submit Report', 'Cancel Submission');
			document.getElementById('bug_report_input').setValue('');
			dialog_input.onconfirm = function() {
				var ajax = new Ajax();
				ajax.responseType = Ajax.JSON;
				ajax.requireLogin = true;
				ajax.post('http://' + hostName + '/jobs/issue/', {'jobid' : jobid, 'info': document.getElementById('bug_report_input').getValue()});
				dialog_input.hide();
				dialog_confirm.showMessage('Report Issue', 'Thank you for reporting an issue with this job. The developer will take a look at it and contact you if nessessary to resolve this issue.')
				setTimeout(function() {dialog_confirm.hide()},6000); 
			}
		},
		start: function(jobid) {
			var ajax = new Ajax();
			ajax.responseType = Ajax.JSON;
			ajax.ondone = function(data) {
				console.log('test');
				jobs.active_jobs.push(jobid);
				console.log(jobs.active_jobs);
				document.getElementById('job_'+data.md5).setInnerXHTML(data.html);
				jobs.attachStatusEvents(data);
			}
			ajax.post('http://' + hostName + '/jobs/start/', {'jobid' : jobid });
		},

		stop: function(jobid) {
			var ajax = new Ajax();
			ajax.responseType = Ajax.JSON;
			ajax.requireLogin = true;
			ajax.ondone = function(data) {
				document.getElementById('job_'+data.md5).setInnerXHTML(data.html);
				jobs.attachStatusEvents(data);
			}
			ajax.post('http://' + hostName + '/jobs/stop/', {'jobid' : jobid} );
		},

		restart: function(jobid,object) {
			var dialog = new Dialog(Dialog.DIALOG_CONTEXTUAL).setContext(object).showChoice('Restart Job', 'Restarting the job will completely reimport all photos into your Facebook account. Are you sure you want to restart the job?', 'Yep', 'Nevermind');
			dialog.onconfirm = function() {

				var ajax = new Ajax();
				ajax.responseType = Ajax.JSON;
				ajax.requireLogin = true;
				ajax.ondone = function(data) {
                                    document.getElementById('job_'+data.md5).setInnerXHTML(data.html);
                                    jobs.attachStatusEvents(data);
                                    jobs.active_jobs.push(jobid);
				}
				ajax.post('http://' + hostName + '/jobs/restart/', {'jobid' : jobid } );
			};
		},
                remove: function(jobid,object) {
                    var dialog = new Dialog(Dialog.DIALOG_POP).showChoice('Delete Job', 'Are you sure you want to delete this job?', 'Yep', 'Nevermind');
			dialog.onconfirm = function() {
				var ajax = new Ajax();
				ajax.responseType = Ajax.JSON;
				ajax.requireLogin = true;
				ajax.ondone = function(data) {
					Animation(document.getElementById('job_'+data.md5)).to('height', '0px').to('opacity', 0).hide().ease(Animation.ease.end).go();
				}
				ajax.post('http://' + hostName + '/jobs/delete/', {'jobid' : jobid } );
			};
                },

		status: function(jobid) {
			if(jobs.active_jobs.length > 0) {
				var ajax = new Ajax();
				ajax.responseType = Ajax.JSON;
                                ajax.requireLogin = true;
				ajax.ondone = function(data) {
					if(data != null) {
                                            jobs.active_jobs = data.active_jobs;
                                            for (i=0;i<data.jobs.length;i++) {
                                                    document.getElementById('job_'+data.jobs[i].md5).setInnerXHTML(data.jobs[i].html);

                                                    jobs.attachStatusEvents(data.jobs[i]);

                                                    if(data.jobs[i].status == 3 || data.jobs[i].status == 6)
                                                            Animation(document.getElementById('job_'+data.jobs[i].md5)).to('background', '#FFFFFF').from('#5DFC0A').go();
                                            }
                                        }
					setTimeout(function() {jobs.status(jobs.active_jobs.join(','))}, '1500');
				}
				ajax.onerror = function() {
					jobs.status(jobs.active_jobs.join(','));
				}
				ajax.post('http://' + hostName + '/jobs/getStatus/', {'jobs' : jobid } );
			}
			else {
				setTimeout(function() {jobs.status(jobs.active_jobs.join(','))}, '1500');
			}
		},

		toggle_completed: function() {
			if(document.getElementById('completed_jobs').getStyle('display') == 'none') {
				Animation(document.getElementById('completed_jobs')).to('height', 'auto').from('0px').from(0).blind().show().ease(Animation.ease.end).go();
				document.getElementById('completed_header').setStyle('backgroundImage', 'url(http://' + hostName + '/img/arrow_up.png)');
			} else {
				Animation(document.getElementById('completed_jobs')).to('height', '0px').hide().ease(Animation.ease.end).go();
				document.getElementById('completed_header').setStyle('backgroundImage', 'url(http://' + hostName + '/img/arrow_down.png)');
			}
		},

		attachStatusEvents: function(data) {
			if(document.getElementById('link_restart_'+data.md5) != null) {
				document.getElementById('link_restart_'+data.md5).addEventListener('click', function(e){
					e.preventDefault();
					jobs.restart(e.target.getName(), e.target);
				});
			}

			if(document.getElementById('link_stop_'+data.md5) != null) {
				document.getElementById('link_stop_'+data.md5).addEventListener('click', function(e){
					e.preventDefault();
					jobs.stop(e.target.getName());
				});
			}

			if(document.getElementById('link_resume_'+data.md5) != null) {
				document.getElementById('link_resume_'+data.md5).addEventListener('click', function(e){
					e.preventDefault();
					jobs.start(e.target.getName());
				});
			}
			
			if(document.getElementById('notify_'+data.md5) != null) {
				document.getElementById('notify_'+data.md5).addEventListener('click', function(e){
					jobs.notify(e.target.getValue(), e.target.getChecked());
				});
			}
			
			if(document.getElementById('link_report_'+data.md5) != null) {
				document.getElementById('link_report_'+data.md5).addEventListener('click', function(e){
					jobs.report(e.target.getName(), e.target);
				});
			}

                        if(document.getElementById('link_delete_'+data.md5) != null) {
				document.getElementById('link_delete_'+data.md5).addEventListener('click', function(e){
                                        e.preventDefault();
					jobs.remove(e.target.getName(), e.target);
				});
			}
		}
	}

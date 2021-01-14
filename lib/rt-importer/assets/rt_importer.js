jQuery(document).ready(function () {
	var count = 1;
	var successCount = 0;
	var failCount = 0;
	var forceImport = false;
	var arr_failed_lead = [];

	jQuery.rtImporter = {
		init: function () {
			jQuery.rtImporter.initMapSubmit();
			jQuery.rtImporter.initSelectDefault();
			jQuery.rtImporter.initOtherField();
			jQuery.rtImporter.initPostData();
			jQuery.rtImporter.initDummyData();
			jQuery.rtImporter.initpogressbar();
			jQuery.rtImporter.initfutureevent();
			jQuery.rtImporter.initmapFieldClick();
			jQuery.rtImporter.initFixedField();
			jQuery.rtImporter.initEnableMapping();
			jQuery.rtImporter.initDeleteMapping();
			jQuery.rtImporter.initAjaxSync();
		},
		initMapSubmit: function () {
			jQuery('#map_submit').click(function () {
				jQuery(this).next('.rt-lib-spinner').show();
				var that = this;
				var data = {
					action: 'rtlib_import',
					type: 'gravity',
					mapSource: jQuery('#mapSource').val(),
					mapPostType: jQuery('#mapPostType').val()
				};

				jQuery.post(ajaxurl, data, function (response) {
					successCount = 0;
					failCount = 0;
					forceImport = false;
					jQuery('#mapping-form').html(response);
					jQuery(that).next('.rt-lib-spinner').hide();

				});
			});
		},
		initSelectDefault: function () {
			jQuery('#rtlibMappingForm .wp-list-table tbody tr').each(function () {
				var tempTD = jQuery(this).children();
				var tempSelectOption = jQuery(tempTD[1]).find('select option');
				var searchQ = jQuery(tempTD[0]).text().trim().toLowerCase();
				jQuery(tempSelectOption).each(function () {
					if (jQuery(this).text().trim().toLowerCase().indexOf(searchQ) !== -1) {
						jQuery(tempTD[1]).find('select').val(jQuery(this).attr('value'));
						jQuery(tempTD[1]).find('select').change();
						return false;
					}
				});

			});
		},
		initOtherField: function () {
			var otherCount = 1;
			jQuery(document).on('change', '.other-field', function () {
				if (jQuery(this).val() === '') {
					return false;
				}
				if (jQuery('#otherfeild' + otherCount).length > 0) {
					return false;
				}
				var tempParent = jQuery(this).parent().parent();
				jQuery(tempParent).after('<tr>' + jQuery(tempParent).html().replace(this.name, 'otherfield' + otherCount).replace(this.name, 'otherfield' + otherCount) + '</tr>');
				otherCount++;
			});
		},
		initPostData: function () {
			jQuery('#map_mapping_import').live('click', function () {
				if (arr_map_fields !== undefined) {
					postdata = {};
					var data = jQuery('#rtlibMappingForm').serializeArray();
					var count = jQuery('#mapEntryCount').val();
					var errorFlag = false;
					jQuery.each(data, function (i, mapping) {
						if (mapping.value === '') {
							return true;
						}

						var temp = mapping.name;
						if (temp.indexOf('default-') > -1) {
							return true;
						}
						if (temp.indexOf('key-') > -1) {
							return true;
						}
						if (temp.indexOf('field-') > -1) {
							//checking Assigned  or not
							if (postdata[mapping.value] === undefined) {

								if (arr_map_fields[mapping.value].multiple) {
									//multiple but assigne first time
									postdata[mapping.value] = Array();
									var tmpObj = {};
									tmpObj.fieldName = mapping.name;
									tmpObj.defaultValue = jQuery(jQuery('#' + mapping.name).parent().next().children('input,select')).val();
									if (arr_map_fields[mapping.value].type !== undefined && arr_map_fields[mapping.value].type === 'defined') {
										var arrMapSelects = jQuery('#' + this.name).siblings('table').find('select');
										if (arrMapSelects.length < 1) {
											errorFlag = true;
											alert('Maping not Defined for ' + arr_map_fields[mapping.value].display_name);
											jQuery('#' + mapping.name).addClass('form-invalid');
											jQuery('#' + mapping.name).focus();
											return false;
										} else {
											var tObj = {};
											jQuery.each(arrMapSelects, function (indx, obj) {
												tObj[jQuery(obj).data('map-value')] = jQuery(this).val();
											});
											tmpObj.mappingData = tObj;
										}

									} else if (arr_map_fields[mapping.value].type === 'key') {
										var arrMapSelects = jQuery('#' + this.name).siblings('select'); // jshint ignore:line
										if (arrMapSelects.length > 0) {
											tmpObj.keyname = jQuery(arrMapSelects).val();
										} else {
											tmpObj.keyname = '';
										}

									} else {

										tmpObj.mappingData = null;
									}

									postdata[mapping.value].push(tmpObj);
								} else {
									//multiple not allowed
									var tmpObj = {}; // jshint ignore:line
									tmpObj.fieldName = mapping.name;
									tmpObj.defaultValue = jQuery(jQuery('#' + mapping.name).parent().next().children('input,select')).val();
									if (arr_map_fields[mapping.value].type !== undefined && arr_map_fields[mapping.value].type === 'defined') {
										var arrMapSelects = jQuery('#' + this.name).siblings('table').find('select'); // jshint ignore:line
										if (arrMapSelects.length < 1) {
											errorFlag = true;
											alert('Maping not Defined for ' + arr_map_fields[mapping.value].display_name);
											jQuery('#' + mapping.name).addClass('form-invalid');
											jQuery('#' + mapping.name).focus();
											return false;
										} else {
											var tObj = {}; // jshint ignore:line
											jQuery.each(arrMapSelects, function (indx, obj) {
												tObj[jQuery(obj).data('map-value')] = jQuery(this).val();
											});
											tmpObj.mappingData = tObj;
										}

									} else if (arr_map_fields[mapping.value].type === 'key') {
										var arrMapSelects = jQuery('#' + this.name).siblings('select'); // jshint ignore:line
										if (arrMapSelects.length > 0) {
											tmpObj.keyname = jQuery(arrMapSelects).val();
										} else {
											tmpObj.keyname = '';
										}

									} else {

										tmpObj.mappingData = null;
									}

									postdata[mapping.value] = tmpObj; //mapping['name'];
								}

							} else {
								if (arr_map_fields[mapping.value].multiple) {
									var tmpObj = {}; // jshint ignore:line
									tmpObj.fieldName = mapping.name;
									tmpObj.defaultValue = jQuery(jQuery('#' + mapping.name).parent().next().children('input,select')).val();
									if (arr_map_fields[mapping.value].type !== undefined && arr_map_fields[mapping.value].type === 'defined') {
										var arrMapSelects = jQuery('#' + this.name).siblings('table').find('select'); // jshint ignore:line
										if (arrMapSelects.length < 1) {
											errorFlag = true;
											alert('Maping not Defined for ' + arr_map_fields[mapping.value].display_name);
											jQuery('#' + mapping.name).addClass('form-invalid');
											jQuery('#' + mapping.name).focus();
											return false;
										} else {
											var tObj = {}; // jshint ignore:line
											jQuery.each(arrMapSelects, function (indx, obj) {
												tObj[jQuery(obj).data('map-value')] = jQuery(this).val();
											});
											tmpObj.mappingData = tObj;
										}

									} else if (arr_map_fields[mapping.value].type === 'key') {
										var arrMapSelects = jQuery('#' + this.name).siblings('select'); // jshint ignore:line
										if (arrMapSelects.length > 0) {
											tmpObj.keyname = jQuery(arrMapSelects).val();
										} else {
											tmpObj.keyname = '';
										}

									} else {

										tmpObj.mappingData = null;
									}

									postdata[mapping.value].push(tmpObj);
								} else {
									errorFlag = true;
									alert('Multiple ' + arr_map_fields[mapping.value].display_name + ' not allowed');
									jQuery('select,input[type=textbox]').each(function (e) {
										if (jQuery(this).val() === mapping.value) {
											jQuery(this).addClass('form-invalid');
										}
									});
									jQuery('#' + mapping.name).addClass('form-invalid');
									jQuery('#' + mapping.name).focus();
									return false;
								}
							}
						} else if (temp.indexOf('otherfield') > -1) {
							var mapElement = jQuery('#' + mapping.name);
							mapping.name = jQuery(mapElement).val();
							if (jQuery.trim(mapping.name) === '') {

							} else if (postdata[mapping.value] === undefined) {
								if (arr_map_fields[mapping.value].multiple) {
									postdata[mapping.value] = Array();
									var tmpObj = {}; // jshint ignore:line
									tmpObj.fieldName = mapping.name;
									tmpObj.defaultValue = '';

									postdata[mapping.value].push(tmpObj);
								} else {
									var tmpObj = {}; // jshint ignore:line
									tmpObj.fieldName = mapping.name;
									tmpObj.defaultValue = '';
									postdata[mapping.value] = tmpObj;
								}

							} else {
								if (arr_map_fields[mapping.value].multiple) {
									var tmpObj = {}; // jshint ignore:line
									tmpObj.fieldName = mapping.name;
									tmpObj.defaultValue = '';
									postdata[mapping.value].push(tmpObj);
								} else {
									errorFlag = true;
									alert('Multiple ' + arr_map_fields[mapping.value].display_name + ' not allowed');
									jQuery('select,input[type=textbox]').each(function (e) {
										if (jQuery(this).val() === mapping.value) {
											jQuery(this).addClass('form-invalid');
										}
									});
									jQuery(mapElement).addClass('form-invalid');
									jQuery(mapElement).focus();
									return false;
								}
							}

						} else {
							if (jQuery('[name=' + mapping.name + ']').parent().parent().css('display') !== 'none') {
								var tmpObj = {}; // jshint ignore:line
								tmpObj.fieldName = mapping.value;
								tmpObj.defaultValue = '';
								if (postdata[mapping.name] === undefined) {
									if (arr_map_fields[mapping.name] !== undefined && arr_map_fields[mapping.name].multiple) {
										tmpObj.mappingData = null;
										postdata[mapping.name] = Array();
										postdata[mapping.name].push(tmpObj);
									} else {
										postdata[mapping.name] = tmpObj;
									}
								} else {
									if (arr_map_fields[mapping.name] !== undefined && arr_map_fields[mapping.name].multiple) {
										tmpObj.mappingData = null;
										postdata[mapping.name].push(tmpObj);
									} else {
										errorFlag = true;
										alert('Multiple ' + arr_map_fields[mapping.name].display_name + ' not allowed');
										jQuery('select,input[type=textbox]').each(function (e) {
											if (jQuery(this).val() === mapping.name) {
												jQuery(this).addClass('form-invalid');
											}
										});
										jQuery('#' + mapping.name).addClass('form-invalid');
										jQuery('#' + mapping.name).focus();
										return false;
									}
								}


							}
						}
					});
					if (errorFlag) {
						return false;
					}
					jQuery.each(arr_map_fields, function (i, map_field) {
						if (map_field.required) {
							if (postdata[map_field.slug] === undefined) {
								alert(map_field.display_name + ' is required');
								errorFlag = true;
								return false;
							}
						}

					});
					if (errorFlag) {
						return false;
					}
					jQuery('#rtlibMappingForm').slideUp();
					jQuery('.myerror').addClass('error');
					jQuery('.myupdate').addClass('updated');
					jQuery('#startImporting').slideDown();
					jQuery('#progressbar').progressbar({
						value: 0, max: arr_lead_id.length
					});

					if (jQuery('#forceimport').attr('checked') === undefined) {
						forceImport = 'false';
					} else {
						forceImport = 'true';
					}
					var rCount = 0;
					var ajaxdata = {
						action: 'rtlib_map_import',
						mapSourceType: jQuery('#mapSourceType').val(),
						mapPostType: jQuery('#mapPostType').val(),
						map_data: postdata,
						map_form_id: jQuery('#mapSource').val(),
						map_row_index: rCount,
						gravity_lead_id: parseInt(arr_lead_id[rCount].id, 10),
						forceimport: forceImport,
						trans_id: transaction_id,
						rthd_module: jQuery('#rthd_module').val()
					};
					try {
						jQuery.rtImporter.do_ajax_in_loop(ajaxdata, rCount);
					} catch (e) {

					}
					return false;
				}
			});
		},
		do_ajax_in_loop: function (ajaxdata, rCount) {
			ajaxdata.map_row_index = rCount;
			var tmpArray = Array();
			var i = 0;
			var limit = 1;
			if (ajaxdata.mapSourceType === 'csv') {
				limit = 10;
			}
			while (i < limit) {
				if (arr_lead_id.length === rCount) {
					break;
				}
				tmpArray.push(arr_lead_id[rCount++].id);
				i++;
			}
			lastCount = i;
			ajaxdata.gravity_lead_id = tmpArray;
			jQuery.post(ajaxurl, ajaxdata, function (response) {
				jQuery.each(response, function (ind, obj) {
					jQuery('#progressbar').progressbar('option', 'value', successCount + failCount + 1);
					if (obj.status) {
						successCount++;
						jQuery('#sucessfullyImported').html(successCount);

					} else {
						failCount++;
						jQuery('#failImported').html(failCount);
						arr_failed_lead.push(obj.lead_id);
					}

				});
				if (arr_lead_id.length > rCount) {
					jQuery.rtImporter.do_ajax_in_loop(ajaxdata, rCount);
				}
			}).fail(function () {
				jQuery('#progressbar').progressbar('option', 'value', successCount + failCount + lastCount);
				failCount += lastCount;
				jQuery('#failImported').html(failCount);
				jQuery.each(ajaxdata.gravity_lead_id, function (ind, obj) {
					arr_failed_lead.push(obj.lead_id);
				});

				if (arr_lead_id.length > rCount) {
					jQuery.rtImporter.do_ajax_in_loop(ajaxdata, rCount);
				}
			});
		},
		initDummyData: function () {
			var lead_index = 0;
			jQuery(document).on('click', "a[href='#dummyDataNext']", function (e) {
				e.preventDefault();
				lead_index++;
				if (arr_lead_id.length - 1 < lead_index) {
					lead_index = 0;
				}
				jQuery.rtImporter.load_dummy_data(lead_index);
			});
			jQuery(document).on('click', "a[href='#dummyDataPrev']", function (e) {
				e.preventDefault();
				lead_index--;
				if (lead_index < 0) {
					lead_index = arr_lead_id.length - 1;
				}
				jQuery.rtImporter.load_dummy_data(lead_index);
			});
		},
		load_dummy_data: function (lead_id) {
			try {
				var ajaxdata = {
					action: 'rtlib_gravity_dummy_data',
					mapSourceType: jQuery('#mapSourceType').val(),
					map_form_id: jQuery('#mapSource').val(),
					dummy_lead_id: arr_lead_id[lead_id].id
				};
				jQuery.post(ajaxurl, ajaxdata, function (response) {
					jQuery('.rtlib-importer-dummy-data').each(function (e, el) {
						var key = jQuery(el).data('field-name');
						if (isNaN(key) && key.indexOf('-s-') > -1) {
							key = key.replace('/-s-/g', ' ');
						}
						jQuery(el).html(response[key]);
					});
				});
			} catch (e) {

			}
		},
		initpogressbar: function () {
			jQuery('#progressbar').live('progressbarcomplete', function (event, ui) {
				jQuery('.importloading').hide();
				jQuery('.sucessmessage').show();
				//            arr_failed_lead.toString()
				var strHTML = '';
				if (arr_failed_lead.toString() !== '') {
					strHTML += 'Fail Lead Index : ' + arr_failed_lead.toString() + '<br />';
				}
				strHTML += '<a target="_blank" href="admin.php?page=rthdlogs&log-list=log-list&trans_id=' + transaction_id + '" >View All Inserted Leads </a>';
				jQuery('#extra-data-importer').html(strHTML);

			});
		},
		initfutureevent: function () {
			jQuery('#futureYes').live('click', function (event, ui) {
				var ajaxdata = {
					action: 'rtlib_map_import_feauture',
					map_data: postdata,
					map_form_id: jQuery('#mapSource').val(),
					mapPostType: jQuery('#mapPostType').val()
				};
				jQuery.post(ajaxurl, ajaxdata, function (response) {
					if (response.status) {
						jQuery('#futureYes').parent().html('<h4 class="rt-import-success-message">Success !</h4>');
					} else {
						jQuery('#futureYes').parent().html('<h4 class="rt-import-warning-message">Already mapped.</h4>');
					}
				});

			});
			jQuery('#futureNo').live('click', function (event, ui) {
				jQuery(this).parent().html('<h3>Done</h3>');
			});
		},
		initmapFieldClick: function () {
			jQuery(document).on('click', "a[href='#mapField']", function (e) {
				e.preventDefault();
				var fieldMap = this;
				if (jQuery(this).next().length > 0) {
					jQuery(this).next().toggle();
					return false;
				}
				var ajaxdata = {
					action: 'rtlib_defined_map_feild_value',
					mapSourceType: jQuery('#mapSourceType').val(),
					map_form_id: jQuery('#mapSource').val(),
					field_id: jQuery(fieldMap).data('field')

				};

				jQuery.post(ajaxurl, ajaxdata, function (response) {
					if (response.length < 1) {
						alert('Too many distinct value, Can\'t Map');
						jQuery('[name=' + jQuery(fieldMap).data('field-name') + ']').parent().parent().show();
						jQuery(fieldMap).prev().addClass('form-invalid');
						jQuery(fieldMap).parent().next().html('');
						jQuery(fieldMap).prev().val('');
						jQuery(fieldMap).remove();
						return false;
					}
					var source = jQuery('#map_table_content').html();
					var template = Handlebars.compile(source);

					var arrTmp = {};
					arrTmp.name = '';
					arrTmp.data = response;
					arrTmp.mapData = window[jQuery(fieldMap).data('map-data')];
					jQuery(fieldMap).after(template(arrTmp));

					jQuery(fieldMap).parent().find('tr').each(function () {
						var tempTD = jQuery(this).children();
						var tempSelectOption = jQuery(tempTD[1]).find('select option');
						var searchQ = jQuery(tempTD[0]).text().trim().toLowerCase();
						jQuery(tempSelectOption).each(function () {
							if (jQuery(this).text().trim().toLowerCase().indexOf(searchQ) !== -1) {
								jQuery(tempTD[1]).find('select').val(jQuery(this).attr('value'));
								jQuery(tempTD[1]).find('select').change();
								return false;
							}
						});

					});


				});

			});
		},
		initFixedField: function () {
			jQuery(document).on('change', '.map_form_fixed_fields', function (e) {
				e.preventDefault();
				var field_name = jQuery(this).val();
				if (field_name === '') {
					return false;
				}
				if (arr_map_fields[field_name].type !== undefined && arr_map_fields[field_name].type !== 'defined') {
					if (jQuery(this).next().length > 0) {
						jQuery('[name=' + jQuery(this).next().data('field-name') + ']').parent().parent().show();
						jQuery(this).next().remove();

						jQuery(this).next().remove();
					}
					if (arr_map_fields[field_name].type === 'key') {
						var source = jQuery('#key-type-option').html();
						var template = Handlebars.compile(source);
						var tmpArr = window[arr_map_fields[field_name].key_list];
						var tmpStr = '<select name="key-' + field_name + '">';
						tmpStr += template(tmpArr) + '</select>';
						jQuery(this).parent().append(tmpStr);
					}
					jQuery(this).parent().next().html('<input type="text" name="default-' + field_name + '" value="" />');


				} else {
					var source = jQuery('#defined_filed-option').html(); // jshint ignore:line
					var template = Handlebars.compile(source); // jshint ignore:line

					var tmpStr = '<select name="default-' + field_name + '">'; // jshint ignore:line
					var tmpArr = window[arr_map_fields[field_name].definedsource]; // jshint ignore:line

					tmpStr += template(tmpArr) + '</select>';
					jQuery(this).parent().next().html(tmpStr);
					if (jQuery(this).next().length < 1) {
						jQuery(this).after('<a data-field-name="' + field_name + '" href="#mapField" data-map-data="' + arr_map_fields[field_name].definedsource + '" data-field="' + this.name.replace('field-', '') + '" > Map </a>');
						if (field_name !== 'product') {
							jQuery('[name=' + field_name + ']').parent().parent().hide();
						}
						jQuery(this).next().click();
					} else {
						jQuery('[name=' + jQuery(this).next().data('field-name') + ']').parent().parent().show();
						jQuery(this).next().remove();
						jQuery(this).next().remove();
						jQuery(this).after('<a data-field-name="' + field_name + '" href="#mapField" data-map-data="' + arr_map_fields[field_name].definedsource + '" data-field="' + this.name.replace('field-', '') + '" > Map </a>');
						if (field_name !== 'product') {
							jQuery('[name=' + field_name + ']').parent().parent().hide();
						}
						jQuery(this).next().click();

					}
				}
			});
		},
		initEnableMapping: function () {
			jQuery('.rtlib_enable_mapping').on('change', function (e) {
				e.preventDefault();
				jQuery(this).next('.rt-lib-spinner').show();
				var update_mapping_id = jQuery(this).data('mapping-id');
				var that = this;
				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'rtlib_enable_mapping',
						mapping_id: update_mapping_id,
						mapping_enable: that.checked

					},
					success: function (data) {
						if (data.status) {

						} else {
							alert('error in updating mapping from server');
						}
						jQuery(that).next('.rt-lib-spinner').hide();
					},
					error: function (xhr, textStatus, errorThrown) {
						alert('error in update while communicating to server');
						jQuery(that).next('.rt-lib-spinner').hide();
					}

				});
			});
		},
		initDeleteMapping: function () {
			jQuery('.rtlib_delete_mapping').on('click', function (e) {
				e.preventDefault();
				var r = confirm('Are you sure you want to remove this Mapping?');
				if (r !== true) {
					e.preventDefault();
					return false;
				}
				jQuery(this).next('.rt-lib-spinner').show();
				var del_mapping_id = jQuery(this).data('mapping-id');
				var that = this;
				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'rtlib_delete_mapping',
						mapping_id: del_mapping_id
					},
					success: function (data) {
						if (data.status) {
							jQuery(that).next('.rt-lib-spinner').hide();
							jQuery('#mapping_' + del_mapping_id).fadeOut(500, function () {
								jQuery(this).remove();
							});
						} else {
							alert('error in delete mapping from server');
						}
					},
					error: function (xhr, textStatus, errorThrown) {
						alert('error in remove ');
						jQuery(that).next('.rt-lib-spinner').hide();
					}

				});
			});
		},
		initAjaxSync: function () {

			jQuery(document).on('click', '.rt-lib-sync', function (e) {
				e.preventDefault();
				form_id = jQuery(this).data('form-id');
				if (window['arr_lead_id_' + form_id].length > 0) {
					jQuery.rtImporter.initAjaxSync_Call(form_id, window['arr_lead_id_' + form_id][0]['id'], 0, jQuery(this));
					jQuery(this).next('.rt-lib-spinner').show();
					jQuery(this).hide();
				}
			});

		},
		initAjaxSync_Call: function (formid, leadid, count, elem) {
			jQuery.ajax({
				url: ajaxurl, type: 'POST', dataType: 'json', data: {
					action: 'rtlib_sync_gf_importer', lead_id: leadid, form_id: formid
				}, success: function (data) {
					if (data.status) {
						if (count < window['arr_lead_id_' + formid].length) {
							jQuery.rtImporter.initAjaxSync_Call(formid, window['arr_lead_id_' + formid][count]['id'], count + 1, elem);
						} else {
							elem.next('.rt-lib-spinner').hide();
							elem.show();
						}
					}
				},
			});

		}
	};
	jQuery.rtImporter.init();
});

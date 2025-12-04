( function( $ ) {

	'use strict';

	// DEBUG: Always show debug status on load
	if ( typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1' ) {
		console.log('%c[SearchWiz DEBUG MODE ACTIVE]', 'background: #ffeb3b; color: #000; font-size: 14px; padding: 5px;');
		console.log('Debug mode enabled via ?searchwiz_debug=1');
	}

	if ( typeof searchwiz_search === 'undefined' || searchwiz_search === null ) {
		if ( typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1' ) {
			console.error('[SearchWiz DEBUG] searchwiz_search object not found! Script will exit.');
		}
		return;
	}

	$( function() {

		// DEBUG: Log page initialization
		if ( typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1' ) {
			console.log('[SearchWiz DEBUG] Document ready - initializing admin scripts');
			console.log('  searchwiz_search object:', searchwiz_search);

			// Log all searchwiz_index fields on the page
			var swIndexFields = $('[name^="searchwiz_index"]');
			console.log('[SearchWiz DEBUG] Found ' + swIndexFields.length + ' searchwiz_index form fields:');
			swIndexFields.each(function(i) {
				var $field = $(this);
				var isCheckbox = $field.attr('type') === 'checkbox';
				console.log('  ' + (i+1) + '. ' + $field.attr('name') +
					' = ' + (isCheckbox ? ($field.is(':checked') ? '✓ CHECKED' : '✗ unchecked') : $field.val()));
			});
		}

		$( window ).on( 'load', function() {
			$( '.is-cb-dropdown .is-cb-multisel' ).css({ 'position': 'absolute', 'display': 'none' });
			$( '.col-wrapper .load-all' ).on( 'click', function() {
				var post_id = $('#post_ID').val();
				var post_type = $(this).attr('id');
				var this_load = $(this);
				var inc_exc = $('.search-form-editor-panel').attr('id');
				$(this).parent().append('<span class="spinner"></span>');
				$.ajax( {
					type : "post",
					url: searchwiz_search.ajaxUrl,
					data: {
						action: 'display_posts',
						post_id: post_id,
						post_type: post_type,
						inc_exc: inc_exc
					},
					success: function( response ) {
						$(this_load).parent().find('select').find('option').remove().end().append(response );
						if ( $(this_load).parent().find('select option:selected').length ) {
							$(this_load).parent().find('.col-title span').html( '<strong>'+$(this_load).parent().find('.col-title').text()+'</strong>');
						}
						$(this_load).parent().find('.spinner').remove();
						$(this_load).remove();
					},
					error: function (request, error) {
						alert( " The posts could not be loaded. Because: " + error );
					}
				} );
			} );
			if( $( '#search-form-editor #is_search_in_header' ).is(':checked') ) {
				$('#search-form-editor .site-uses-cache-wrapper').show();
			}
		} );

			$( document ).ready( function() {
						var url = window.location.href;
						var args = url.split('=').pop();
						if ( 'menu-search' === args ) {
							$('#toplevel_page_ivory-search .wp-submenu-wrap li, #toplevel_page_ivory-search .wp-submenu-wrap li a').removeClass('current');
							$('#toplevel_page_ivory-search .wp-submenu-wrap li:nth-child(3), #toplevel_page_ivory-search .wp-submenu-wrap li:nth-child(3) a').addClass('current');
						}
			} );

            var dateFormat = "d-m-yy",
                  from = $( "#is-after-datepicker" )
                    .datepicker({
                    dateFormat : 'd-m-yy',
                    changeMonth: true,
                    changeYear: true
                    })
                    .on( "change", function() {
                      to.datepicker( "option", "minDate", isgetDate( this ) );
                    }),
                  to = $( "#is-before-datepicker" ).datepicker({
                    dateFormat : 'd-m-yy',
                    changeMonth: true,
                    changeYear: true
                  })
                  .on( "change", function() {
                    from.datepicker( "option", "maxDate", isgetDate( this ) );
                  });

                function isgetDate( element ) {
                  var date;
                  try {
                    date = $.datepicker.parseDate( dateFormat, element.value );
                  } catch( error ) {
                    date = null;
                  }
                  return date;
                }

		// REMOVED: Accordion functionality (jQuery UI accordion)
		// All sections now display fully expanded - no collapsible panels needed

		// Initialize color picker - Following FiboSearch approach
	// Save immediately on change with debouncing to avoid spam while dragging
	if ($('.is-colorpicker').length > 0) {
		var addedClearer = false;
		$('.is-colorpicker').wpColorPicker({
			palettes: false,
			change: function(event, ui) {
				var $input = $(event.target);
				var newColor = ui.color.toString();

				// Update the input value
				$input.val(newColor);

				// Debounce auto-save to avoid too many requests while dragging
				clearTimeout($input.data('colorTimer'));
				$input.data('colorTimer', setTimeout(function() {
					if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
						console.log('[SearchWiz DEBUG] Color changed to:', newColor, '- triggering auto-save');
					}
					$input.trigger('change');
				}, 300)); // 300ms debounce

				// Handle the "Clear" button (like FiboSearch does)
				// https://github.com/Automattic/Iris/issues/57#issuecomment-899685019
				if (!addedClearer) {
					$('.wp-picker-clear').on('click', function() {
						var $clearInput = $(this).parent('.wp-picker-input-wrap').find('input.wp-color-picker');
						if ($clearInput.length > 0) {
							if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
								console.log('[SearchWiz DEBUG] Color cleared - triggering auto-save');
							}
							$clearInput.val('');
							$clearInput.trigger('change');
						}
					});
					addedClearer = true;
				}
			}
		});
	}

		$('#search-body select[multiple] option').mousedown(function(e) {
			if ($(this).attr('selected')) {
				$(this).removeAttr('selected');
				return false;
			}
		} );

		$( ".col-title .list-search" ).keyup(function() {
			var search_val = $(this).val().toLowerCase();
			var search_sel = $(this).parent().parent().find('select option');
			$( search_sel ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( search_val ) === -1 ) {
					$(this).fadeOut( 'fast' );
				} else {
					$(this).fadeIn( 'fast' );
				}
			} );
		} );

		$( ".list-search.wide" ).keyup(function() {
			var search_val = $(this).val().toLowerCase();
			var search_sel = $(this).parent().find('select option');
			$( search_sel ).each(function() {
				if ( $(this).text().toLowerCase().indexOf( search_val ) === -1 ) {
					$(this).fadeOut( 'fast' );
				} else {
					$(this).fadeIn( 'fast' );
				}
			} );
		} );

		if ( '' === $( '#title' ).val() ) {
			$( '#title' ).focus();
		}

                if ( 0 !== $( '#title' ).length ) {
                    searchwiz_search.titleHint();
                }

		// REMOVED: "Unsaved changes" warning (beforeunload handler)
		// Old search form editor used manual Save button and needed this warning
		// New settings pages use auto-save pattern (like Overview tab theme integration)
		// All SearchWiz admin pages now use the new auto-save architecture - no warnings needed!

		// Tooltip only Text
		$('#search-body #titlewrap').hover(function(){
			if($(this).find("#title[disabled]").length){
			// Hover over code
			var title = $(this).find("#title[disabled]").attr('title');
			$(this).find("#title[disabled]").data('tipText', title).removeAttr('title');
			$('<p class="title_tooltip"></p>')
			.text(title)
			.appendTo('body');
			}
		}, function() {
			// Hover out code
			$(this).find("#title[disabled]").attr('title', $(this).find("#title[disabled]").data('tipText'));
			$('.title_tooltip').remove();
		}).mousemove(function(e) {
			var mousex = e.pageX + 20; //Get X coordinates
			var mousey = e.pageY - 40; //Get Y coordinates
			$('.title_tooltip')
			.css({ top: mousey, left: mousex })
		});

		$('#search-form-editor-tabs li').hover(function(){
			// Hover over code
			var title = $(this).find("a").attr('title');
			$(this).find("a").data('tipText', title).removeAttr('title');
			$('<p class="title_tooltip '+$(this).attr('id')+'"></p>')
			.text(title)
			.appendTo('body')
			.fadeIn('slow');
		}, function() {
			// Hover out code
			$(this).find("a").attr('title', $(this).find("a").data('tipText'));
			$('.title_tooltip.'+$(this).attr('id')).remove();
		}).mousemove(function(e) {
			var mousex = e.pageX + 20; //Get X coordinates
			var mousey = e.pageY - 40; //Get Y coordinates
			$('.title_tooltip.'+$(this).attr('id'))
			.css({ top: mousey, left: mousex })
		});

	} );

	searchwiz_search.titleHint = function() {
		var $title = $( '#title' );
		var $titleprompt = $( '#title-prompt-text' );

		if ( '' === $title.val() ) {
			$titleprompt.removeClass( 'screen-reader-text' );
		}

		$titleprompt.click( function() {
			$( this ).addClass( 'screen-reader-text' );
			$title.focus();
		} );

		$title.blur( function() {
			if ( '' === $(this).val() ) {
				$titleprompt.removeClass( 'screen-reader-text' );
			}
		} ).focus( function() {
			$titleprompt.addClass( 'screen-reader-text' );
		} ).keydown( function( e ) {
			$titleprompt.addClass( 'screen-reader-text' );
			$( this ).unbind( e );
		} );
	};

        $(".is-cb-dropdown .is-cb-title").on('click', function() {
            if ( $( this ).hasClass('is-dropdown-toggle') ){
                $( this ).removeClass('is-dropdown-toggle');
            } else {
                $( this ).addClass('is-dropdown-toggle');
            }
          $( this ).parents(".is-cb-dropdown").find(".is-cb-multisel").slideToggle('fast');
        });

        $(document).bind('click', function(e) {
          var $clicked = $(e.target);
          if (!$clicked.parents().hasClass("is-cb-dropdown")) {
              $(".is-cb-dropdown .is-cb-multisel").hide();
              $( '.is-cb-title' ).removeClass('is-dropdown-toggle');
          }
        });

        $('.is-cb-multisel input[type="checkbox"]').on('click', function() {

          var title = $(this).val();
          var title2 = $(this).parent().text().trim();

          if ($(this).is(':checked')) {
            var html = '<span title="' + title + '"> ' + title2 + '</span>';
            $( this ).parents(".is-cb-dropdown").find('.is-cb-titles').append(html);
            $( this ).parents(".is-cb-dropdown").find(".is-cb-select").hide();
            $( '.form-table h3.post-type-'+title ).show();
            $( '.form-table .post-type-'+title ).show().removeClass('is-ptype-hidden');
          } else {
            $( '.form-table .post-type-'+title ).hide().addClass('is-ptype-hidden');
			$( '.form-table .post-type-'+title+' #'+title+'-post-search_all' ).trigger( 'click' );
            $( this ).parents(".is-cb-dropdown").find('.is-cb-titles span[title="' + title + '"]').remove();
            if ( 0 === $( this ).parents(".is-cb-dropdown").find( '.is-cb-titles span' ).length ) {
                $( this ).parents(".is-cb-dropdown").find(".is-cb-select").show();
            }
          }
        });

        $( '#search-form-editor .is-mime-select, #search-form-editor .is-post-select, #search-form-editor .is-tax-select, #search-form-editor .is-meta-select' ).each( function() {
            if ( $( this ).is( ':checked' ) ) {
                if ( 'all' === $( this ).val() ) {
                    if ( $( this ).hasClass( 'is-post-select' ) ) {
                        $( this ).closest( 'div' ).find( '.is-posts' ).hide();
                    } else if ( $( this ).hasClass( 'is-tax-select' ) ) {
                        $( this ).closest( 'div' ).find( '.is-taxes' ).hide();
                    } else if ( $( this ).hasClass( 'is-mime-select' ) ) {
                        $( this ).closest( 'div' ).find( '.is-mime' ).hide();
                    }
                }
                if ( $( this ).hasClass( 'is-meta-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-metas' ).show();
                }
            } else {
                if ( $( this ).hasClass( 'is-meta-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-metas' ).hide();
                }                
            }
        } );

        $( '#search-form-editor .is-post-select, #search-form-editor .is-tax-select, #search-form-editor .is-meta-select, #search-form-editor .is-mime-select' ).on( 'click', function(e) {
			// Cancels the default actions.
			e.stopPropagation();  
            if ( $( this ).hasClass( 'is-meta-select' ) ) {
                if ( $( this ).is( ':checked' ) ) {
                    $( this ).closest( 'div' ).find( '.is-metas' ).show();
                } else {
                    $( this ).closest( 'div' ).find( '.is-metas' ).hide();
                    $( this ).closest( 'div' ).find( '.is-metas select option').prop( 'selected', false );
                }
            } else if ( 'selected' === $( this ).val() ) {
                if ( $( this ).hasClass( 'is-post-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-posts' ).show();
                    $( this ).closest( 'div' ).find( '.notice-sw-info' ).hide();
                } else if ( $( this ).hasClass( 'is-tax-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-taxes' ).show();
                } else if ( $( this ).hasClass( 'is-mime-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-mime' ).show();
                }
            } else {
                if ( $( this ).hasClass( 'is-post-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-posts' ).hide();
					$( this ).closest( 'div' ).find( '.notice-sw-info' ).show();
                    $( this ).closest( 'div' ).find( '.is-posts select option').prop( 'selected', false );
                } else if ( $( this ).hasClass( 'is-tax-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-taxes' ).hide();
                    $( this ).closest( 'div' ).find( '.is-taxes select option').prop( 'selected', false );
                } else if ( $( this ).hasClass( 'is-mime-select' ) ) {
                    $( this ).closest( 'div' ).find( '.is-mime' ).hide();
                    $( this ).closest( 'div' ).find( '.is-mime select option').prop( 'selected', false );
                }
            }
        } );


        $('.includes_extras input[type="checkbox"]').on('click', function() {

          if ( ! $(this).is(':checked') ) {
              if ( ! $( this ).closest( '.includes_extras' ).find( 'input[type="checkbox"]:checked' ).length ) {
                  alert('Please make sure that you have configured the search form to search any content');
              }
          }
        } );

        $( '#search-form-editor .is-mime option' ).on( 'click', function() {
            if ( ! $( this ).is(':checked') ) {
                if ( 0 === $('.is-mime select option:selected').length ) {
                    if ( $('#includes').length ) {
                        $('.search-attachments').prop( "checked", true );
                    } else {
                        $('.search-attachments').prop( "checked", false );
                    }
                }
            } else {
                is_mime_multi_option_selected();
            }
        } );

        $( '#search-form-editor .is-mime-select' ).on( 'click', function() {
            if ( 'all' === $( this ).val() ) {
                if ( $('#excludes').length ) {
                    $('.search-attachments').prop( "checked", false );
                } else {
                    $('.search-attachments').prop( "checked", true );
                }
            }
        } );

        if ( 0 !== $('.is-mime select option:selected').length ) {
            is_mime_multi_option_selected();
        }

        function is_mime_multi_option_selected(){
                var temp_value = ['image', 'video', 'audio', 'text', 'pdf'];
                $.each(temp_value, function(key, value) {
                if ( 0 === $('.is-mime select option[value*="'+value+'"]:selected').length ) {
                    $('.search-attachments[name*="'+value+'"]').prop( "checked", false );
                } else {
                    $('.search-attachments[name*="'+value+'"]').prop( "checked", true );
                }
                } );

                if ( 0 === $('.is-mime select option[value*="doc"]:selected').length && 0 === $('.is-mime select option[value*="excel"]:selected').length && 0 === $('.is-mime select option[value*="word"]:selected').length ) {
                    $('.search-attachments[name*="doc"]').prop( "checked", false );
                } else {
                    $('.search-attachments[name*="doc"]').prop( "checked", true );
                }            
        }

        $( '.search-attachments-wrapper' ).show();

        $('.search-attachments').on('click', function() {
            if ( 0 === $('.is-mime select option:selected').length ) {
                if ( ! $(this).hasClass('exclude') ){
                if ( $( this ).is(':checked') ) {
                    return;
                } else {
                    alert('You can configure it to exclude from search in the search form Exclude section');
                    return false;
                }
                }
            }
            var search_name = $( this ).attr('name');
            var search_value = '';

            switch(search_name) {
              case 'search_images':
                  search_value = 'image';
                break;
              case 'search_videos':
                  search_value = 'video';
                break;
              case 'search_audios':
                  search_value = 'audio';
                break;
              case 'search_text':
                  search_value = 'text';
                break;
              case 'search_pdfs':
                  search_value = 'pdf';
                break;
              case 'search_docs':
                  search_value = ['doc', 'excel', 'word'];
                break;
            }
            if ( '' !== search_value ) {
                if ( Array.isArray( search_value ) ) {
                    var this2 = this;
                    $.each(search_value, function(key, value) {
                    if ( $( this2 ).is(':checked') ) {
                        $('.is-mime select option[value*="'+value+'"]').attr('selected', 'selected');
                    } else {
                        $('.is-mime select option[value*="'+value+'"]').removeAttr('selected');
                    }
                    });
                } else {
                if ( $( this ).is(':checked') ) {
                    $('.is-mime select option[value*="'+search_value+'"]').attr('selected', 'selected');
                } else {
                    $('.is-mime select option[value*="'+search_value+'"]').removeAttr('selected');
                }
                }
            }
                if ( ! $(this).hasClass('exclude') && 0 === $('.is-mime select option:selected').length ) {
                    $('.search-attachments').prop( "checked", true );
                }
        } );

        $( '#search-form-editor .post-type-attachment .is-posts option' ).on( 'click', function() {
            if ( 0 === $('#search-form-editor .post-type-attachment .is-posts option:selected').length ) {
				$( '.is-mime-radio, .search-attachments-wrapper' ).show();            	
            } else {
            	$( '.is-mime-radio, .is-mime, .search-attachments-wrapper' ).hide();
            }
        } );

        $('#search-form-editor #is_search_in_header').on('click', function() {
            if( $( this ).is(':checked') ) {
                $('#search-form-editor .site-uses-cache-wrapper').show();
            } else {
                $('#search-form-editor .site-uses-cache-wrapper').hide();
				$('#search-form-editor #is_site_uses_cache').prop( "checked", false );
            }
        } );

        $('#search-form-editor .not_load_files').each(function() {
            if( ! $( this ).is(':checked') ) {
                $( this ).parent().next('.not-load-wrapper').hide();
            }
        } );

        $('#search-form-editor .not_load_files').on('click', function() {
            if( $( this ).is(':checked') ) {
                $( this ).parent().next('.not-load-wrapper').show();
            } else {
                $( this ).parent().next('.not-load-wrapper').hide();
            }
        } );

	function toggle_highlight_color_inputs() {
		if( $( '._is_settings-highlight_terms' ).is(':checked') ) {
			$( '.highlight-container' ).removeClass('is-field-disabled').show();
		} else {
			$( '.highlight-container' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_highlight_color_inputs();
	$( '._is_settings-highlight_terms' ).on( 'click', function() {
		toggle_highlight_color_inputs();
	} );

	function toggle_menu_search_inputs() {
		if( $( '.searchwiz_search_locations' ).is(':checked') || $( '.searchwiz_search_menu_name' ).is(':checked') ) {
			$( '.menu-settings-container' ).removeClass('is-field-disabled').show();
		} else {
			$( '.menu-settings-container' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_menu_search_inputs();
	$( '.searchwiz_search_locations, .searchwiz_search_menu_name' ).on( 'click', function() {
		toggle_menu_search_inputs();
	} );

	function toggle_menu_style_inputs( style ) {
		if( ( undefined !== style && 'default' !== style ) || ! $('#is_menu_styledefault').is(":checked") ) {
			$( '.form-style-dependent' ).removeClass('is-field-disabled').show();
		} else {
			$( '.form-style-dependent' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_menu_style_inputs();
	$( '.searchwiz_search_style' ).on( 'click', function() {
		toggle_menu_style_inputs($(this).val());
	} );

	function toggle_description_inputs() {
		if( $( '#_is_ajax-show_description' ).is(':checked') ) {
			$( '._is_ajax-description_source_wrap, ._is_ajax-description_length_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-description_source_wrap, ._is_ajax-description_length_wrap' ).addClass('is-field-disabled').hide();
		}
	}

	toggle_description_inputs();
	$( '._is_ajax-description_wrap .check-radio' ).on( 'click', function() {
		toggle_description_inputs();
	} );

	function toggle_details_box_fields() {
		if( $( '#_is_ajax-show_details_box' ).is(':checked') && ( ( $( '#_is_ajax-show_matching_categories' ).is(':checked') || $( '#_is_ajax-show_matching_tags' ).is(':checked') ) ) ) {
			$( '._is_ajax-product_list_wrap, ._is_ajax-order_by_wrap, ._is_ajax-order_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-product_list_wrap, ._is_ajax-order_by_wrap, ._is_ajax-order_wrap' ).addClass('is-field-disabled').hide();
		}
	}
        toggle_details_box_fields();
	$( '#_is_ajax-show_details_box, #_is_ajax-show_matching_categories, #_is_ajax-show_matching_tags' ).on( 'click', function() {
                    toggle_details_box_fields();
	} );

	function toggle_show_more_result_textbox_fields() {
		if( $( '#_is_ajax-show_more_result' ).is(':checked') ) {
			$( '._is_ajax-more_result_text_wrap, ._is_ajax-show_more_func_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-more_result_text_wrap, ._is_ajax-show_more_func_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_show_more_result_textbox_fields();
	$( '._is_ajax-show_more_result_wrap .check-radio' ).on( 'click', function() {
		toggle_show_more_result_textbox_fields();
	} );

	function toggle_show_view_all_textbox_fields() {
		if( $( '#_is_ajax-view_all_results' ).is(':checked') ) {
			$( '._is_ajax-view_all_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-view_all_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_show_view_all_textbox_fields();
	$( '._is_ajax-view_all_results_wrap .check-radio' ).on( 'click', function() {
		toggle_show_view_all_textbox_fields();
	} );

	function toggle_more_result_fields() {
		if( $( '#_is_ajax-show_more_result' ).is(':checked') ) {
			$( '._is_ajax-more_result_text_wrap' ).removeClass('is-field-disabled').show();
		} else {
			$( '._is_ajax-more_result_text_wrap' ).addClass('is-field-disabled').hide();
		}
	}
	toggle_more_result_fields();
	$( '._is_ajax-show_more_result_wrap .check-radio' ).on( 'click', function() {
		toggle_more_result_fields();
	} );

	// Enable Customize Fields.
	function toggle_enable_customize() {
		if( $( '#_is_customize-enable_customize' ).is(':checked') ) {
			$( '.form-table-panel-customize .is-field-wrap' ).removeClass('is-field-disabled');
		} else {
			$( '.form-table-panel-customize .is-field-wrap' ).addClass('is-field-disabled');
		}
	}

	toggle_enable_customize();

	// Displays customizer enable confirmation alert.
	function enable_customizer_alert() {
        window.setTimeout(function () {
			var r = confirm("A page reload is required for this change.");
			if ( r == true ) {
				toggle_enable_customize();
				$('#is-admin-form-element').submit();
			} else {
				if( $( '#_is_customize-enable_customize' ).is(':checked') ) {
					$('#_is_customize-enable_customize').prop('checked', false);
				} else {
					$('#_is_customize-enable_customize').prop('checked', true);
				}
			}
        }, 300);
	}
	
	$( '#_is_customize-enable_customize' ).on( 'click', function() {
		enable_customizer_alert();
	} );

	$( '.form-table-panel-customize .is-field-disabled-message .message' ).on( 'click', function() {
		$( '#_is_customize-enable_customize' ).trigger('click');

	} );

	// Enable AJAX.
	function toggle_enable_ajax() {
		if( $( '#_is_ajax-enable_ajax' ).is(':checked') ) {
			$( '.form-table-panel-ajax .is-field-wrap' ).removeClass('is-field-disabled');
		} else {
			$( '.form-table-panel-ajax .is-field-wrap' ).addClass('is-field-disabled');
		}
	}

	// Displays AJAX enable confirmation alert.
	function enable_ajax_alert() {
        window.setTimeout(function () {
			var r = confirm("A page reload is required for this change.");
			if ( r == true ) {
				toggle_enable_ajax();
				$('#is-admin-form-element').submit();
			} else {
				if( $( '#_is_ajax-enable_ajax' ).is(':checked') ) {
					$('#_is_ajax-enable_ajax').prop('checked', false);
				} else {
					$('#_is_ajax-enable_ajax').prop('checked', true);
				}
			}
        }, 300);
	}

	$( '#_is_ajax-enable_ajax' ).on( 'click', function() {
		enable_ajax_alert();
	} );

	$( '.form-table-panel-ajax .is-field-disabled-message .message' ).on( 'click', function() {
		$( '#_is_ajax-enable_ajax' ).trigger('click');

	} );

	function toggle_analytics_info(){
		if ( '1' === $( '#is_disable_analytics' ).val() ) {
			$( '#is_disable_analytics' ).closest( 'div' ).find( '.analytics-info' ).hide();
		} else {
			$( '#is_disable_analytics' ).closest( 'div' ).find( '.analytics-info' ).show();
		}
	}
	toggle_analytics_info();

	$('#is_disable_analytics').on('click', function() {
		toggle_analytics_info();
	} );

	/**
	 * Expand panel when link is clicked.
	 * 
	 * The link is used in conflict info about anywhere 
	 * fuzzy match and index search engine.
	 * 
	 * @since 1.0.0
	 */
	// REMOVED: Accordion panel clicking (no longer used)

	/**
	 * Ajax Requesting flag.
	 * 
	 * @since 1.0.0
	 * @var bool 
	 */
	 var is_requesting = false;

	/**
	 * Create, resume and recreate index ajax request.
	 * 
	 * @since 1.0.0
	 * @param array data {
	 *      @type string ajax_url The ajax request url.
	 *      @type string method The request method.
	 *      @type string action The request action name to fire wp_ajax.
	 *      @type string _isnonce The security nonce to validate the request.
	 *      @type int page The page request number.
	 *      @type string start_msg The start message feedback to the user.
	 *      @type string btn_label The button label.
	 * }
	 */
	function is_index_create( data ) {
		is_show_loader( true );
		is_disable_index_controls( true );
		is_update_progress_bar( true, data );
		// REMOVED: Accordion collapse click (no longer used)

		if( ! is_requesting ) {
			is_requesting = true;
			$.ajax( {
				url: data.ajax_url,
				method: data.method,
				data: data,
				error: function( xhr, ajaxOptions, thrownError ) {
					const data = xhr.responseJSON;
					console.log( data );
					is_requesting = false;
					is_show_loader( false );
					is_disable_index_controls( false );
					is_start_timer( false );
				}
			} ).done( function ( ret ) {
				is_requesting = false;
				ret = JSON.parse( ret );
				is_show_loader( false );
				is_update_progress_bar( true, ret );
	
				if( 'pausing' == data.idx_status && 'created' != ret.idx_status ) {
					ret.idx_status = 'paused';
				}
				is_results_add( ret.results, true );
				is_manage_idx_create_btn( ret );
			} );
		}
	}

	/**
	 * Handle btn state change actions.
	 * 
	 * @since 1.0.0
	 * @var int 
	 */	
	function is_manage_idx_create_btn( data ) {
		const btn = $( '#is_index_create_btn' );

		btn.data( 'is', data );
		const label = data.btn_labels[ data.idx_status ];
		btn.val( label );
		btn.prop( 'disabled', false );

		switch( data.idx_status ) {
			case 'created':
				is_disable_index_controls( false );
				is_start_timer( false );
				break;

			case 'pausing':
				is_disable_index_controls( true, true );
				is_start_timer( false );
				break;

			case 'paused':
				is_disable_index_controls( false );
				is_start_timer( false );
				break;
	
			case 'empty':
			case 'creating':
				is_index_create( data );
				is_start_timer( true );
				break;
		}

	}

	/**
	 * Create button click event handler.
	 * 
	 * Show starting message and start execution timer.
	 * 
	 * @since 1.0.0
	 */
	$( '#is_index_create_btn' ).on( 'click', function(e) {
		e.preventDefault();
		const data = $( this ).data( 'is' );
		switch( data.idx_status ) {
			case 'creating':
				data.idx_status = 'pausing';
				break;

			case 'empty':
			case 'created':
				is_results_add( "\n" + data.start_msg );
			case 'paused':
				data.idx_status = 'creating';
				break;
		}
		is_manage_idx_create_btn( data );
	} );

	/**
	 * Scroll textarea to the bottom.
	 * 
	 * @since 1.0.0
	 * @param string text The Text to append.
	 * @param bool clear_first Clear the textearea before adding.
	 */
	function is_results_add( text, clear_first ) {
		if( text ) {
			const results_el = $( '#is-index-status' );
			let results = text;
			if( ! clear_first ) {
				results = results_el.val() + "\n" + text;
			}
			results_el.val( results );
			results_el.scrollTop(results_el[0].scrollHeight);	
		}
	}

	/**
	 * Disable index settings controls while a request is running.
	 *
	 * Only disables index-related controls, NOT the entire form.
	 * This allows users to continue using other settings while indexing runs.
	 *
	 * @since 1.0.0
	 */
	 function is_disable_index_controls( disable, enable_create_btn ) {
		// Only disable index-specific controls, not the entire form
		$( '#is_index_delete_btn' ).prop( 'disabled', disable );
		$( '#is_index_post_btn' ).prop( 'disabled', disable );
		$( '#is_index_post_txt' ).prop( 'disabled', disable );

		const btn_disable = enable_create_btn ? true : false;
		$( '#is_index_create_btn' ).prop( 'disabled', btn_disable );
	}

	/**
	 * Delete all index ajax request.
	 * 
	 * @since 1.0.0
	 * @param array data {
	 *      @type string ajax_url The ajax request url.
	 *      @type string method The request method.
	 *      @type string action The request action name to fire wp_ajax.
	 *      @type string _isnonce The security nonce to validate the request.
	 *      @type string start_msg The start message feedback to the user.	 * 
	 * }
	 */
	function is_index_delete( data ) {
		is_show_loader( true );
		is_disable_index_controls( true );
		$.ajax( {
			url: data.ajax_url,
			method: data.method,
			data: data,
			error: function( xhr, ajaxOptions, thrownError ) {
				const data = xhr.responseJSON;
				console.log( data );
				is_show_loader( false );
				is_disable_index_controls( false );
			}
		} ).done( function ( ret ) {
			ret = JSON.parse( ret );
			is_show_loader( false );

			is_results_add( ret.results, true );
			if( ret.btn_label ) {
				const btn = $( '#is_index_create_btn' );
				btn.val( ret.btn_label );
				const data = btn.data( 'is' );
				data.page = 1;
				btn.data( 'is', data );
			}

			is_disable_index_controls( false );
		} );
	}

	/**
	 * Delete index button click event handler.
	 * 
	 * Show starting message.
	 * 
	 * @since 1.0.0
	 */
	$( '#is_index_delete_btn' ).on( 'click', function(e) {
		e.preventDefault();
		is_update_progress_bar( false );

		const data = $( this ).data( 'is' );
		is_results_add( data.start_msg );
		is_index_delete( data );
		is_start_timer( false, true );
	} );

	/**
	 * Index a post ajax request.
	 * 
	 * @since 1.0.0
	 * @param array data {
	 *      @type string ajax_url The ajax request url.
	 *      @type string method The request method.
	 *      @type string action The request action name to fire wp_ajax.
	 *      @type string _isnonce The security nonce to validate the request.
	 *      @type int $post_id The post ID to request ajax. It is set on event handler.
	 *      @type string start_msg The start message feedback to the user.
	 * }
	 */
	 function is_index_post( data ) {
		is_show_loader( true );
		is_disable_index_controls( true );
		$.ajax( {
			url: data.ajax_url,
			method: data.method,
			data: data,
			error: function( xhr, ajaxOptions, thrownError ) {
				const data = xhr.responseJSON;
				console.log( data );
				is_show_loader( false );
				is_disable_index_controls( false );
			}
		} ).done( function ( ret ) {
			ret = JSON.parse( ret );
			is_show_loader( false );

			is_results_add( ret.results );
			is_disable_index_controls( false );
		} );
	}

	/**
	 * Index Post button click event handler.
	 * 
	 * Show starting message. 
	 * Set post_id from text field.
	 * 
	 * @since 1.0.0
	 */
	$( '#is_index_post_btn' ).on( 'click', function(e) {
		e.preventDefault();
		is_update_progress_bar( false );

		let data = $( this ).data( 'is' );
		is_results_add( "\n" + data.start_msg );
		data.post_id = $( '#is_index_post_txt' ).val();		
		is_index_post( data );
	} );

	/**
	 * Show loading feedback.
	 * 
	 * @since 1.0.0
	 */
	function is_show_loader( show ) {
		if( show ) {
			$( '.is-loader' ).show();
		}
		else {
			$( '.is-loader' ).hide();
		}
	}

	/**
	 * Time elapsed var.
	 * 
	 * @since 1.0.0
	 * @var int 
	 */
	var is_time_elapsed = 0;

	/**
	 * Interval ID ref to clear afterwards.
	 * 
	 * @since 1.0.0
	 * @var int 
	 */
	var interval_id = 0;

	/**
	 * Start elapsed execution timer interval.
	 * 
	 * Call is_update_time_elapsed function every second.
	 * 
	 * @since 1.0.0
	 * @param bool start If true, start the timer, stop otherwise.
	 */	
	function is_start_timer( start, reset ) {
		if( reset ) {
			is_time_elapsed = 0;
		}
		if( start ) {

			$( '#is_time_elapsed_wrap' ).show();

			if( ! interval_id ) {
				interval_id = window.setInterval( is_update_time_elapsed, 1000 );
			}
		}
		else {
			window.clearInterval( interval_id );
			interval_id = 0;
		}
	}
	
	/**
	 * Update time elapsed label every second. 
	 * 
	 * @since 1.0.0
	 */	
	function is_update_time_elapsed() {
		is_time_elapsed++;
		const time = new Date( is_time_elapsed * 1000 )
						.toISOString()
						.substr( 11, 8 )
						+ '  ' 
						+ '.'.repeat( is_time_elapsed % 4 );

		$( '#is_time_elapsed' ).html( time );
	}

	/**
	 * Updates the progress bar.
	 * 
	 * @since 1.0.0
	 * @param bool show True to show, false to hide.
	 * @param array data The data received from server.
	 */	
	function is_update_progress_bar( show, data ) {
		if( show ) {
			$( '#is_progress' ).show();
			let percentage = Math.round( data.indexed / data.total * 100 );
			percentage += '%';
			$( '#is_indicator' ).css( 'width', percentage );	
		}
		else {
			$( '#is_progress' ).hide();
			$( '#is_time_elapsed_wrap' ).hide();
		}
	}
	/**
	 * Toggle taxonomies select input.
	 * 
	 * Index settings - taxonomies section.
	 * 
	 * @since 1.0.0
	 */
	 function toggle_taxonomies_select() {
		if( 'select' == $( '.is_index_taxonomies_opt:checked' ).val() ) {
			$( '.is-index-tax-select' ).show();
		} else {
			$( '.is-index-tax-select' ).hide();
		}
	}
	toggle_taxonomies_select();
	
	/**
	 * Toggle taxonomies select input event handler.
	 * 
	 * @since 1.0.0
	 */
	 $( '.is_index_taxonomies_opt' ).on( 'click', function() {
		toggle_taxonomies_select();
	} );

	/**
	 * Toggle meta fields select input.
	 * 
	 * Index settings - meta fields section.
	 * 
	 * @since 1.0.0
	 */
	 function toggle_index_meta_fields_inputs() {
		if( 'select' == $( '.is_index_meta_fields_opt:checked' ).val() ) {
			$( '.is-index-metas' ).show();
		} else {
			$( '.is-index-metas' ).hide();
		}
	}
	toggle_index_meta_fields_inputs();
	
	/**
	 * Toggle meta fields select input event handler.
	 * 
	 * @since 1.0.0
	 */
	 $( '.is_index_meta_fields_opt' ).on( 'click', function() {
		toggle_index_meta_fields_inputs();
	} );

	/**
	 * Reset index settings button event handler.
	 *
	 * @since 1.0.0
	 */
	 $( '#is-index-reset' ).on( 'click', function(e) {
		const data = $( this ).data( 'is' );
		if( data && data.confirm_msg && confirm( data.confirm_msg ) ) {
			$( 'form' ).prop( 'action', '' );
			$( 'form input[name="action"]' ).val( data.action );
			$( 'form input[name="_wpnonce"]' ).val( data._wpnonce );
			return true;
		}
		return false;
	} );

	/**
	 * Global Auto-save functionality
	 *
	 * @since 1.0.0
	 */
	function showGlobalAutosave() {
		var notification = $('#sw_global_autosave');
		notification.css('opacity', '1');
		setTimeout(function() {
			notification.css('opacity', '0');
		}, 2000);
	}

	// Auto-save all form fields
	$('#searchwiz_search_options').on('change', 'input, select, textarea', function(event) {
		try {
			var $changedField = $(this);
			var fieldName = $changedField.attr('name');
			var fieldValue = $changedField.val();
			var fieldType = $changedField.attr('type');

			// DEBUG: Log what triggered the save
			if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
				console.log('[SearchWiz DEBUG] Auto-save triggered');
				console.log('  Field name:', fieldName);
				console.log('  Field type:', fieldType);
				console.log('  Field value:', fieldValue);
				if (fieldType === 'checkbox' || fieldType === 'radio') {
					console.log('  Is checked:', $changedField.is(':checked'));
				}
			}

			var $form = $(this).closest('form');
			var formData = $form.serialize();

		// DEBUG: Log serialized form data
		if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
			console.log('  Serialized form data length:', formData.length);
			console.log('  First 200 chars:', formData.substring(0, 200));
		}

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'searchwiz_autosave_settings',
				form_data: formData,
				nonce: typeof searchwiz_search !== 'undefined' && searchwiz_search.autosaveNonce ? searchwiz_search.autosaveNonce : ''
			},
			success: function(response) {
				// DEBUG: Log response
				if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
					console.log('[SearchWiz DEBUG] AJAX Success');
					console.log('  Response:', response);
				}

				if (response.success) {
					showGlobalAutosave();

					// DEBUG: Confirm save
					if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
						console.log('  ✓ Settings saved successfully');
					}
				} else {
					// DEBUG: Log failure
					if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
						console.error('  ✗ Save failed:', response);
					}
				}
			},
			error: function(xhr, status, error) {
				// DEBUG: Log AJAX errors
				if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
					console.error('[SearchWiz DEBUG] AJAX Error');
					console.error('  Status:', status);
					console.error('  Error:', error);
					console.error('  Response:', xhr.responseText);
				}
			}
		});
		} catch (err) {
			// Silently catch errors from browser extensions interfering with form events
			if (typeof searchwiz_debug !== 'undefined' && searchwiz_debug === '1') {
				console.warn('[SearchWiz DEBUG] Error caught in auto-save (likely browser extension):', err);
			}
		}
	});

	// Custom CSS Help Toggle
	$('.sw-css-help-toggle').on('click', function(e) {
		e.preventDefault();
		$('.sw-css-examples').slideToggle(300);
		$(this).find('.dashicons').toggleClass('dashicons-editor-help dashicons-no-alt');
	});

	// Copy CSS Example to Textarea
	$('.sw-copy-css').on('click', function(e) {
		e.preventDefault();
		var $example = $(this).closest('.sw-css-example');
		var cssCode = $example.attr('data-css');
		var $textarea = $('#custom_css');

		// Decode HTML entities
		cssCode = $('<div/>').html(cssCode).text();

		// Get current value
		var currentValue = $textarea.val().trim();

		// Add CSS with proper spacing
		if (currentValue) {
			$textarea.val(currentValue + '\n\n' + cssCode);
		} else {
			$textarea.val(cssCode);
		}

		// Trigger change event to activate auto-save
		$textarea.trigger('change');

		// Scroll to textarea
		$('html, body').animate({
			scrollTop: $textarea.offset().top - 100
		}, 500);
	});

	/**
	 * Theme Integration checkbox auto-save handler.
	 *
	 * @since 1.1.0
	 */
	$('#searchwiz_theme_integration_enabled').on('change', function() {
		var isEnabled = $(this).is(':checked');
		var saveNotification = $('#searchwiz_theme_integration_saved');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'searchwiz_save_theme_integration',
				enabled: isEnabled ? 'on' : 'off',
				nonce: typeof searchwiz_search !== 'undefined' && searchwiz_search.themeIntegrationNonce ? searchwiz_search.themeIntegrationNonce : ''
			},
			success: function(response) {
				if (response.success) {
					// Show saved notification
					saveNotification.css('opacity', '1');
					setTimeout(function() {
						saveNotification.css('opacity', '0');
					}, 2000);

					// Reset the "unsaved changes" warning
					// Update the checkbox's default state to match current state
					var checkbox = document.getElementById('searchwiz_theme_integration_enabled');
					if (checkbox) {
						checkbox.defaultChecked = isEnabled;
					}
				}
			}
		});
	});

	/**
	 * Default Search checkbox auto-save handler.
	 *
	 * @since 1.1.0
	 */
	$('#searchwiz_default_search_enabled').on('change', function() {
		var isEnabled = $(this).is(':checked');
		var saveNotification = $('#searchwiz_default_search_saved');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'searchwiz_save_default_search',
				enabled: isEnabled ? 'on' : 'off',
				nonce: typeof searchwiz_search !== 'undefined' && searchwiz_search.defaultSearchNonce ? searchwiz_search.defaultSearchNonce : ''
			},
			success: function(response) {
				if (response.success) {
					// Show saved notification
					saveNotification.css('opacity', '1');
					setTimeout(function() {
						saveNotification.css('opacity', '0');
					}, 2000);

					// Reset the checkbox's default state
					var checkbox = document.getElementById('searchwiz_default_search_enabled');
					if (checkbox) {
						checkbox.defaultChecked = isEnabled;
					}
				}
			}
		});
	});

	/**
	 * Shortcode copy button handler.
	 *
	 * @since 1.1.0
	 */
	$('.sw-copy-shortcode').on('click', function(e) {
		e.preventDefault();
		var shortcode = $(this).data('shortcode');
		var button = $(this);

		// Create temporary input to copy from
		var temp = $('<input>');
		$('body').append(temp);
		temp.val(shortcode).select();
		document.execCommand('copy');
		temp.remove();

		// Show feedback
		var originalText = button.text();
		var copiedText = typeof searchwiz_search !== 'undefined' && searchwiz_search.i18n && searchwiz_search.i18n.copied ? searchwiz_search.i18n.copied : 'Copied!';
		button.text(copiedText);
		button.addClass('button-primary');

		setTimeout(function() {
			button.text(originalText);
			button.removeClass('button-primary');
		}, 2000);
	});

	/**
	 * Upgrade CTA click tracking handler.
	 *
	 * @since 1.1.0
	 */
	$('.sw-upgrade-cta').on('click', function(e) {
		var source = $(this).data('source');
		var section = $(this).data('section');

		// Send tracking data via AJAX
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'searchwiz_track_upgrade_click',
				source: source,
				section: section,
				nonce: typeof searchwiz_search !== 'undefined' && searchwiz_search.trackUpgradeNonce ? searchwiz_search.trackUpgradeNonce : ''
			}
		});

		// Don't prevent default - let the link work
	});

} )( jQuery );

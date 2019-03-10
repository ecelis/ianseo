var dwData;

$(document).ready(function() {
    getMatchesData();
});

function getMatchesData() {
    $.getJSON(WebDir+'Final/Spotting-getEvents.php', function (data) {
        if (data.error == 0) {
            dwData = data.data;
            updateComboEvents();
        }
    });
}

function updateComboEvents() {
    $('#Spotting').hide();
    var spType = $('#spotType').val();
    $('#spotCode').empty();
    $('#spotCode').append('<option value="">---</option>');
    $('#spotPhase').empty();
    $('#spotPhase').append('<option value="">---</option>');
    $('#spotMatch').empty();
    $('#spotMatch').append('<option value="">---</option>');
    $.each(dwData[spType], function (i, item) {
        $('#spotCode').append('<option value="'+i+'"'+(PreEvent==i ? ' selected="selected"' : '')+'>'+i+' - '+item.name+'</option>');
    });
    if(PreEvent!='') {
        updateComboPhases();
    }
}

function updateComboPhases() {
    $('#Spotting').hide();
    var spType = $('#spotType').val();
    var spEvent = $('#spotCode').val();
    $('#spotPhase').empty();
    $('#spotPhase').append('<option value="">---</option>');
    $('#spotMatch').empty();
    $('#spotMatch').append('<option value="">---</option>');
    if(spEvent!='') {
        $.each(dwData[spType][spEvent]['phases'], function (i, item) {
            $('#spotPhase').append('<option value="'+item.id+'"'+(PrePhase==item.id ? ' selected="selected"' : '')+'>'+item.name+'</option>');
            if(PrePhase>=0) {
                updateComboMatches();
            }
        });
    }
}

function updateComboMatches(selectedMatch) {
    $('#Spotting').hide();
    var spType = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spPhase = $('#spotPhase').val();
    $('#spotMatch').empty();
    $.getJSON(WebDir+'Final/Spotting-MatchesList.php?CompCode='+CompCode+'&Type='+spType+'&Event='+spEvent+'&Phase='+spPhase, function (data) {
        if(data.data.length!=1) {
            $('#spotMatch').append('<option value="">---</option>');
        }
        $.each(data.data, function (i, item) {
            if(item.LeftOpponent.TeamCode != null && item.RightOpponent.TeamCode != null) {
                var text='';
                if(item.Prefix != '') {
                    text = item.Prefix + ' - ';
                }
                if(item.Type=='1') {
                    text+=item.LeftOpponent.TeamName + ' - ' + item.RightOpponent.TeamName;
                } else {
                    text+=item.LeftOpponent.FamilyName + ' ' + item.LeftOpponent.GivenName + ' (' + item.LeftOpponent.TeamCode + ') - ' +
                        item.RightOpponent.FamilyName + ' ' + item.RightOpponent.GivenName + ' (' + item.RightOpponent.TeamCode + ')';
                }
                $('#spotMatch').append('<option value="' + item.MatchId + '"'+((PreMatchno==item.MatchId || selectedMatch==item.MatchId) ? ' selected="selected"' : '')+'>' + text + '</option>');
            }
        });
        if(PreMatchno>=0 || selectedMatch>0) {
            buildScorecard();
        }
    });
}

$(document).keyup(function(e) {
    // check if an active arrow cell has focus
    if($('.ActiveArrow input:focus').length>0) {
        return;
    }

    // if the key is a star on its own
    if(e.key=='*' && !e.shiftKey && !e.metaKey && !e.ctrlKey && !e.altKey) {
        // Select the activearrow tabindex and star the previous one
        var tabindex=parseInt($('.ActiveArrow input').attr('tabIndex'));
        if(tabindex>1) {
            var previous=$('[tabindex="'+(tabindex-1)+'"]');
            if(previous.length==1) {
                var val= previous.val();
                if(val.substr(-1)=='*') {
                    previous.val(val.substr(0,val.length-1));
                } else {
                    previous.val(val+'*');
                }
                updateArrow(previous[0]);
            }
        }
    }

    // it is a tab (ASC=9)
    if(e.which==9 && !e.metaKey && !e.ctrlKey && !e.altKey) {
        var tabindex=parseInt($('.ActiveArrow input').attr('tabIndex'));
        // moves forward or backwards depending on the shift key
        if(e.shiftKey) {
           if(tabindex>1) {
               tabindex--;
           }
        } else {
            tabindex++;
        }
        var newCell=$('[tabindex="'+tabindex+'"]');
        if(newCell.length==1) {
            selectArrow(newCell[0], true);
        }
    }
});

function toggleTarget() {
    $('#Target').toggleClass('Hidden', $('#spotTarget:checked').length==0);
    buildScorecard();
}

function toggleAlternate() {
    if($('.Alternate:hidden').length>0) {
        $('.Alternate').show();
        var Ends=$('table.Scorecard').attr('ends');
        var Arrows=$('table.Scorecard').attr('arrows');
        var SO=$('table.Scorecard').attr('so');
        var tabindex=1;
        $('.ShootsFirst:checked').each(function() {
            // gets the rows set as shooting first and set them as alternates
            // r1=$()
        });
    } else {
        $('.Alternate').hide();
        $('[tabindexorg]').each(function() {
            this.prop('tabindex', this.attr('tabindexorg'));
        });
    }
}

// some global variables needed to spot
var SvgCursor;

function buildScorecard() {
    $('#Spotting').hide();
    $('.ActiveArrow').toggleClass('ActiveArrow', false);
    $('#Target').toggleClass('TargetL', false).toggleClass('TargetR', false);

    var spTeam = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spMatch = $('#spotMatch').val();
    var spTarget = $('#spotTarget:checked').length>0;
    $.getJSON(WebDir+'Final/Spotting-getScorecards.php?Team='+spTeam+'&Event='+spEvent+'&MatchId='+spMatch+(spTarget ? '&ArrowPosition=1' : ''), function (data) {
        if(data.error!=0) {
            return;
        }

        $('#OpponentNameL').html(data.nameL);
        $('#OpponentNameR').html(data.nameR);
        $('#ScorecardL').html(data.scoreL);
        $('#ScorecardR').html(data.scoreR);

        $('#MatchAlternate').prop('checked', data.isAlternate);
        if(data.isAlternate) {
            $('.Alternate').show();
        } else {
            $('.Alternate').hide();
        }

        if(data.isLive) {
            $('#liveButton').val(TurnLiveOff).toggleClass('Live', true);
        } else {
            $('#liveButton').val(TurnLiveOn).toggleClass('Live', false);
        }

        $('#OpponentNameL').toggleClass('Winner', data.winner=='L');
        $('#OpponentNameR').toggleClass('Winner', data.winner=='R');
        $('#ScorecardL').toggleClass('Winner', data.winner=='L');
        $('#ScorecardR').toggleClass('Winner', data.winner=='R');
        if(data.confirmed) {
            $('#OpponentNameL').toggleClass('Confirmed', data.winner=='L');
            $('#OpponentNameR').toggleClass('Confirmed', data.winner=='R');
            $('#ScorecardL').toggleClass('Confirmed', data.winner=='L');
            $('#ScorecardR').toggleClass('Confirmed', data.winner=='R');
            $('#confirmMatch').attr('disabled', true);
        }

        if(spTarget) {
            var TgtOrgSize=data.targetSize;
            var TgtSize=Math.min($('#Content').width()/3, $('#Content').height() - $('#MatchSelector').outerHeight() - 75);
            var zoom=data.targetZoom;
            $('#Target').html(data.target).width(TgtSize).height(TgtSize);
            SvgCursor=$('#Target #SvgCursor circle');
            $('.SVGTarget')
                .width(TgtSize)
                .height(TgtSize)
                .attr('OrgSize', TgtOrgSize)
                .mousemove(function(e) {
                    var activeArrow=$('.ActiveArrow input');
                    if(activeArrow.length==1) {
                        var ratio = TgtOrgSize/ TgtSize;
                        var w = parseInt(TgtOrgSize / zoom);
                        var x = parseInt(e.offsetX * ratio);
                        var y = parseInt(e.offsetY * ratio);
                        $(this).attr('viewBox', (x - x / zoom) + ' ' + (y - y / zoom) + ' ' + (w) + ' ' + (w));
                        SvgCursor.attr('cx', x).attr('cy', y).show();
                    }
                })
                .click(function(e) {
                    var activeArrow=$('.ActiveArrow input');
                    if(activeArrow.length==1) {
                        var realsize = parseInt($(this).attr('realsize'));
                        var TgtOrgSize=$(this).attr('OrgSize');
                        var ratio = TgtOrgSize / TgtSize;
                        var convert=realsize/(TgtOrgSize-80);
                        var w = parseInt(TgtOrgSize / zoom);
                        var x = (parseInt(e.offsetX) * ratio - TgtOrgSize/2)*convert;
                        var y = (parseInt(e.offsetY) * ratio - TgtOrgSize/2)*convert;
                        var position='&x='+x+'&y='+y;
                        if(e.which==3) {
                            position+='&noValue=1';
                        }
                        updateArrow(activeArrow[0], position);
                        SvgCursor.hide();
                    }
                })
                .contextmenu(function(e) {
                    var activeArrow=$('.ActiveArrow input');
                    if(activeArrow.length==1) {
                        var realsize = parseInt($(this).attr('realsize'));
                        var TgtOrgSize=$(this).attr('OrgSize');
                        var ratio = TgtOrgSize / TgtSize;
                        var convert=realsize/(TgtOrgSize-80);
                        var w = parseInt(TgtOrgSize / zoom);
                        var x = (parseInt(e.offsetX) * ratio - TgtOrgSize/2)*convert;
                        var y = (parseInt(e.offsetY) * ratio - TgtOrgSize/2)*convert;
                        var position='&x='+x+'&y='+y;
                        position+='&noValue=1';
                        updateArrow(activeArrow[0], position);
                        SvgCursor.hide();
                        return false;
                    }
                })
                .mouseleave(function(e) {
                    $(this).attr('viewBox', '0 0 '+(TgtOrgSize)+' '+(TgtOrgSize));
                    SvgCursor.hide();
                });
        }

        $('input[id^="Arrow["]').keydown(function(e) {
            // if the key is a star on its own
            if(e.key=='*' && !e.shiftKey && !e.metaKey && !e.ctrlKey && !e.altKey) {
                // check if an active arrow cell has focus
                var val = this.value;
                if (val.substr(-1) == '*') {
                    this.value = val.substr(0, val.length - 1);
                } else {
                    this.value = val + '*';
                }
                this.select();
                e.preventDefault();
            }
        });

        $('#Spotting').show();
        if(spTarget) {
            var TgtSize=Math.min($('#Content').width() - $('#ScorecardL').outerWidth() - $('#ScorecardR').outerWidth(), $('#Content').height() - $('#MatchSelector').outerHeight() - 75);
            $('#Target').width(TgtSize).height(TgtSize);
            $('.SVGTarget')
                .width(TgtSize)
                .height(TgtSize);
        }
        minTabEmpty=999;
        $('[id^="Arrow"]').each(function() {
            if(this.value=='' && $(this).prop('tabIndex') < minTabEmpty) {
                minTabEmpty = $(this).prop('tabIndex');
                $('[id="'+this.id+'"]').focus();
            }
        });
    });
}

function updateArrow(obj, position) {
    var spType = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spMatch = $('#spotMatch').val();
    var spTarget = $('#spotTarget:checked').length>0;
    var valChanged = (obj.value!=obj.defaultValue);

    $.getJSON(WebDir+'Final/Spotting-setArrow.php?Team='+spType+'&Event='+spEvent+'&MatchId='+spMatch+'&'+obj.id+'='+obj.value+(spTarget ? '&ArrowPosition=1' : '')+(position ? position : ''), function (data) {
        if(data.error!=0) {
            return;
        }
        $('#OpponentNameL').toggleClass('Winner', data.winner=='L');
        $('#OpponentNameR').toggleClass('Winner', data.winner=='R');
        $('#ScorecardL').toggleClass('Winner', data.winner=='L');
        $('#ScorecardR').toggleClass('Winner', data.winner=='R');
        $('.Confirmed').toggleClass('Confirmed', false);

        $('[id="'+data.arrowID+'"]').val(data.arrowValue);
        $(data.t).each(function() {
            $('[id="'+this.id+'"]').html(this.val);
        });

        var expand=$('.SVGTarget').attr('convert');
        var TgtCenter=$('.SVGTarget').attr('OrgSize')/2;
        $(data.p).each(function() {
            $('.SVGTarget [id="'+this.id+'"]').attr('cx', this.data.X*expand + TgtCenter).attr('cy', this.data.Y*expand + TgtCenter);
        });

        if(position) {
            var NextTabIndex=$('[tabindex="'+(parseInt($(obj).attr('tabIndex'))+1)+'"]');
            if(NextTabIndex.length>0) {
                selectArrow(NextTabIndex[0], true);
            }
        }

        if(valChanged || data.changed==1) {
            $('[id="'+data.confirm+'"]').attr('disabled', false);
        }

        if(data.newSOPossible) {
            $('.newSoNeeded').show();
        } else {
            $('.newSoNeeded').hide();
        }
    });
}

function setShootingFirst(obj, tabindex) {
    $.getJSON(WebDir+"Final/Spotting-setShootingFirst.php?" + obj.id + "="  +  (obj.checked ? 'y' : 'n'), function(data) {
        if (data.error==0) {
            // sets the tabindex values of the next end!
            var i='';
            $(data.t).each(function() {
                if(i=='' || this.val==tabindex) {
                    i=this.id;
                }
                $('[id="'+this.id+'"]').prop('tabIndex', this.val);
            });
            if(i!='') {
                $('[id="'+i+'"]').focus();
            }
        }
    });
}

function selectArrow(obj, noselect) {
    $('.ActiveArrow').toggleClass('ActiveArrow', false);
    $(obj).parent().toggleClass('ActiveArrow', true);
    $('#Target').toggleClass('TargetL', false).toggleClass('TargetR', false);
    $('[id^="SvgEndL_"]').hide();
    $('[id^="SvgEndR_"]').hide();
    $('[id^="SvgEndL_SO_"]').hide();
    $('[id^="SvgEndR_SO_"]').hide();
    var so=$(obj).closest('tr').attr('so')=='1';
    var end=$(obj).closest('tr').attr('end');
    if($(obj).closest('td.Opponents').attr('id')=='ScorecardL') {
        $('#Target').toggleClass('TargetL', true);
        if(so) {
            $('#SvgEndL_SO_'+end).show();
        } else {
            $('#SvgEndL_'+end).show();
        }
    } else {
        $('#Target').toggleClass('TargetR', true);
        if(so) {
            $('#SvgEndR_SO_'+end).show();
        } else {
            $('#SvgEndR_'+end).show();
        }
    }
    if(noselect==true) {
        return;
    }
    obj.select();
}

function setLive() {
    var spType = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spMatch = $('#spotMatch').val();


    $.getJSON(WebDir+"Final/Spotting-UpdateLive.php?d_Event=" + spEvent + "&d_Match="  +  spMatch + "&d_Team=" + spType, function(data) {
        if(data.error==0) {
            if(data.isLive) {
                $('#liveButton').val(TurnLiveOff).toggleClass('Live', true);
            } else {
                $('#liveButton').val(TurnLiveOn).toggleClass('Live', false);
            }
        } else {
            alert(data.msg);
        }
    });
}

function ConfirmEnd(obj) {
    var spType = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spMatch = $('#spotMatch').val();

    $.getJSON(WebDir+'Final/Spotting-SetConfirmation.php?team=' + spType + '&event=' + spEvent + '&' + obj.id + "=y", function(data) {
        if(data.error==0) {
            if(data.starter!='') {
                // sets the shooting first selector
                var shootingFirst=$('[id="'+data.starter+'"]');
                if(shootingFirst.length>0) {
                    shootingFirst.attr('checked', true);
                    setShootingFirst(shootingFirst[0], data.tabindex);
                }
            }

            // sets the the confirmation!
            obj.disabled=true;

            $('#OpponentNameL').toggleClass('Winner', data.winner=='L');
            $('#OpponentNameR').toggleClass('Winner', data.winner=='R');
            $('#ScorecardL').toggleClass('Winner', data.winner=='L');
            $('#ScorecardR').toggleClass('Winner', data.winner=='R');
            $('.Confirmed').toggleClass('Confirmed', false);


            $('#confirmMatch').attr('disabled', true);
            if(data.winner!='') {
                // match is over, asks confirmation
                $('#confirmMatch').attr('disabled', false);
            }
        }
    });
}

function confirmMatch(obj) {
    var spType = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spMatch = $('#spotMatch').val();

    $.getJSON(WebDir+'Final/Spotting-setConfirmationMatch.php?match='+ spMatch + '&team=' + spType + '&event=' + spEvent, function(data) {
        if (data.error==0) {
            // sets the winner
            $('#OpponentNameL').toggleClass('Confirmed', data.winner=='L');
            $('#OpponentNameR').toggleClass('Confirmed', data.winner=='R');
            $('#ScorecardL').toggleClass('Confirmed', data.winner=='L');
            $('#ScorecardR').toggleClass('Confirmed', data.winner=='R');
            if(data.winner!='') {
                $('.ActiveArrow').toggleClass('ActiveArrow', false);
                $('#Target').toggleClass('TargetL', false).toggleClass('TargetR', false);
                obj.disabled=true;
            }
        }
    });
}

function addStar (id) {
    tmp = $('[id="'+id+'"]').val();
    if(tmp != '') {
        if(tmp.indexOf('*')==-1) {
            tmp += '*';
        } else {
            tmp = tmp.replace('*','');
        }
        $('[id="'+id+'"]').val(tmp);
        updateArrow($('[id="'+id+'"]').get(0));
    }
}

function addPoint (id) {
    tmp = $('[id="'+id+'"]').val();
    if(tmp != '') {
        var spType = ($('#spotType').val()=='Team' ? '1' : '0');
        var spEvent = $('#spotCode').val();

        $.getJSON(WebDir+'Final/Spotting-getNextValidValue.php?Team='+spType+'&Event='+spEvent+'&CurValue='+tmp, function (data) {
            if (data.error != 0) {
                return;
            } else {
                $('[id="' + id + '"]').val(data.nextValue);
                updateArrow($('[id="'+id+'"]').get(0));
            }
        });
    }
}

function moveToNextPhase() {
    var spType = ($('#spotType').val()=='Team' ? '1' : '0');
    var spEvent = $('#spotCode').val();
    var spMatch = $('#spotMatch').val();

    $.getJSON(WebDir + 'Final/Spotting-nextPhase.php?event='+spEvent+'&team='+spType+'&matchno='+spMatch, function(data) {
        if(data.error==0) {
            updateComboMatches(spMatch);
            alert(data.msg);
        }
    });
}

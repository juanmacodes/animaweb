jQuery(document).ready(function ($) {
    $('.vote-btn').on('click', function () {
        var btn = $(this);
        var duelId = btn.data('duel');
        var contender = btn.data('contender');
        var arena = $('#duel-' + duelId);

        if (btn.hasClass('voted')) return;

        btn.addClass('loading').text('...');

        $.ajax({
            url: anima_game_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'anima_vote_duel',
                nonce: anima_game_vars.nonce,
                duel_id: duelId,
                contender: contender
            },
            success: function (res) {
                if (res.success) {
                    // Update UI
                    var v1 = res.data.v1;
                    var v2 = res.data.v2;
                    var total = res.data.total;

                    var p1 = total > 0 ? Math.round((v1 / total) * 100) : 50;
                    var p2 = total > 0 ? Math.round((v2 / total) * 100) : 50;

                    arena.find('.contender-card[data-id="1"] .vote-bar').css('width', p1 + '%');
                    arena.find('.contender-card[data-id="1"] .vote-count').text(v1);

                    arena.find('.contender-card[data-id="2"] .vote-bar').css('width', p2 + '%');
                    arena.find('.contender-card[data-id="2"] .vote-count').text(v2);

                    // Disable buttons
                    arena.find('.vote-btn').prop('disabled', true).addClass('voted').text('VOTED');
                    btn.text('THANKS!');
                } else {
                    alert(res.data);
                    btn.removeClass('loading').text('VOTE');
                }
            },
            error: function () {
                alert('Error de conexión');
                btn.removeClass('loading').text('VOTE');
            }
        });
    });
});

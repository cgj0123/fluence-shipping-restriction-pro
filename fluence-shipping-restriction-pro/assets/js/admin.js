jQuery(function ($) {

    // 初始化产品多选（Ajax 搜索）
    $('#fsrp_products').select2({
        ajax: {
            url: FSRP_AJAX.url,
            dataType: 'json',
            delay: 200,
            data: function (params) {
                return {
                    action: 'fsrp_product_search',
                    nonce: FSRP_AJAX.nonce,
                    q: params.term
                };
            },
            processResults: function (data) {
                return { results: data };
            }
        },
        width: '100%',
        placeholder: 'Search & select products...',
        minimumInputLength: 1
    });

    // 普通多选下拉框
    $('#fsrp_categories, #fsrp_countries').select2({
        width: '100%'
    });

    // 保存规则
    $('#fsrp_save').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $status = $('#fsrp_status');

        $btn.prop('disabled', true);
        $status.text('Saving...');

        $.post(FSRP_AJAX.url, {
            action: 'fsrp_save_rule',
            nonce: FSRP_AJAX.nonce,
            name: $('#fsrp_name').val(),
            products: $('#fsrp_products').val(),
            categories: $('#fsrp_categories').val(),
            countries: $('#fsrp_countries').val(),
            mode: $('#fsrp_mode').val(),
            message: $('#fsrp_message').val()
        }, function (res) {
            if (res.success) {
                $status.text('Saved! Reloading page...');
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                $status.text('Failed to save rule.');
                $btn.prop('disabled', false);
            }
        }).fail(function () {
            $status.text('Error occurred.');
            $btn.prop('disabled', false);
        });
    });

    // 统计图表（仅在数据存在且 Chart 库加载成功后渲染）
    if (window.FSRP_CHART_DATA && window.FSRP_CHART_DATA.length && typeof Chart !== 'undefined') {
        var ctx = document.getElementById('fsrpChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: window.FSRP_LABELS || [],
                datasets: [{
                    label: 'Block Hits',
                    data: window.FSRP_CHART_DATA,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    } else if (window.FSRP_CHART_DATA && !window.FSRP_CHART_DATA.length) {
        document.getElementById('fsrpChart')?.insertAdjacentHTML('afterend', '<p class="description">No statistics yet. Create a rule and it will be tracked.</p>');
    }
});
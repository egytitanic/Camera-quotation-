<?php
function generate_pagination($total_records, $current_page, $records_per_page, $base_url = '?') {
    $total_pages = ceil($total_records / $records_per_page);
    if ($total_pages <= 1) {
        return '';
    }

    $html = '<nav><ul class="pagination justify-content-center">';

    // Previous page
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=' . ($current_page - 1) . '">السابق</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">السابق</span></li>';
    }

    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=' . $i . '">' . $i . '</a></li>';
        }
    }

    // Next page
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . 'page=' . ($current_page + 1) . '">التالي</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">التالي</span></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
?>
<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "user";

// 連接到資料庫
$conn = mysqli_connect($host, $username, $password, $database);

$product_name = $_POST['data'];

if (isset($_POST["data"])) {
    if ($product_name !== '') {
        $query = "SELECT * FROM product WHERE product_name LIKE '%$product_name%'";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
            $html_content = '';
            $products_per_row = 3; // 每行顯示的產品數量
            $count = 0; // 計數器
            $keywords = [];

            $html_content .= '<div class="porfolio-gallery">';
            while ($row = mysqli_fetch_assoc($result)) {
                $keywords[] = $row['product_name'];
                if ($count % $products_per_row === 0) { //用餘數判斷一行三個
                    $html_content .= '<tr>';
                }
                $html_content .= '<td width="350">';
                $html_content .= '<div class="prt-card">';
                $html_content .= '<div class="prt-image">';
                $html_content .= '<img src="' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['product_name']) . '"style="max-width: 100%; max-height: 100%;">';
                $html_content .= '<div class="prt-overlay">';
                $html_content .= '<span class="prt-iocn" style="--i:0.15s">';
                $html_content .= '<a href="shop.php?id=' . htmlspecialchars($row['product_id']) . '"><i class="uil uil-link-h"></i></a>';
                $html_content .= '</span>';
                $html_content .= '</div>';
                $html_content .= '</div>';

                $html_content .= '<div class="prt-desc">';
                $html_content .= '<h3>' . htmlspecialchars($row['product_name']) . '</h3>';
                $html_content .= '<span class= "btn secondary-btn sm">';
                $html_content .= '<a href="shop.php?id=' . htmlspecialchars($row['product_id']) . '">Read more</a>';
                $html_content .= '</span>';
                $html_content .= '</div>';
                $html_content .= '</div>';
                $html_content .= '</td>';
                $html_content .= '</div>';
                if (($count + 1) % $products_per_row === 0 || $count ===    mysqli_num_rows($result) - 1) { //判斷加一後是否能整除或者等於最後一個資料
                    $html_content .= '</tr>';
                }
                $count++;
            }
            echo json_encode(['success' => true, 'html' => $html_content, 'keylist' => $keywords]);
        }
    } else {
        echo json_encode(false);
    }
} else {
    echo json_encode(false);
}

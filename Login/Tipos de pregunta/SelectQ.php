
<?php require '../check_session.php'; ?>

<!DOCTYPE html>
<html>
    <head>
        <title>Math Duck</title>
        <link rel="stylesheet" href="styles.css">
        <style>
            .header-nav {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding: 10px 0;
            }
            
            .back-button {
                background-color: #6c757d;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                text-decoration: none;
                font-size: 14px;
                transition: all 0.3s;
            }
            
            .back-button:hover {
                background-color: #545b62;
                transform: translateX(-2px);
            }
            
            .back-button.admin {
                background-color: ##007bff;
            }
            
            .back-button.admin:hover {
                background-color: #0056b3;
            }
            
            .user-info {
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>

    <body>
        <!-- NAVEGACIÓN SIMPLE -->
        <div class="header-nav">
            <button id="back-button" class="back-button <?php echo $is_admin ? 'admin' : ''; ?>" onclick="goBack()">
                <?php echo $is_admin ? '← Admin Panel' : '← Inicio'; ?>
            </button>
            <div class="user-info">
                <span><?php echo $current_user_email . ' (' . ($is_admin ? 'Admin' : 'Profesor') . ')'; ?></span>
            </div>
        </div>

        <h2 class="t1">Seleciona el tipo de pregunta </h2>
        <p>Elige el tipo de pregunta que deseas responder</p>
        
        <div class="container">
            <a href="Multiple\Multiple.html">
                <img src="images\Multiple.png" alt="MultipleQ">
            </a>
        </div>
        
        <div class="container">
            <a href="OpenQ\OpenQ.html">
                <img src="images\OpenQ.png" alt="OpenQ">
            </a>
        </div>

        <div class="container">
            <a href="TrueorFalse\TrueorFalse.html">
                <img src="images\TrueorFalse.png" alt="TrueorFalse">
            </a>
        </div>

        <script>
            function goBack() {
                window.location.href = '<?php echo $home_url; ?>';
            }
        </script>
    </body>
</html>
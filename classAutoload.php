<?php
class ClassAutoLoad {
    public function __construct() {
        spl_autoload_register([$this, 'loadClass']);
    }
    
    public function loadClass($className) {
        $file = $className . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

class Layouts {
    public function header($conf) {
        echo '
        <!DOCTYPE html>
        <html>
        <head>
            <title>'.$conf['site_name'].'</title>
            <style>
                body { font-family: Arial; max-width: 600px; margin: 0 auto; padding: 20px; }
                .car-form { background: #f9f9f9; padding: 20px; border-radius: 5px; }
                input, select { width: 100%; padding: 10px; margin: 5px 0; }
                button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
                button:hover { background: #0056b3; }
            </style>
        </head>
        <body>
        <h1>'.$conf['site_name'].'</h1>';
    }
    
    public function welcome($conf) {
        echo '<p>Welcome to our Car Sales Management System — book your car today!</p>';
    }
    
    public function footer($conf) {
        echo '
        <footer>
            <p>Contact: '.$conf['site_email'].'</p>
        </footer>
        </body>
        </html>';
    }
}

class Forms {
    public function carPurchaseForm() {
        $currentDate = date('Y-m-d');
        echo '
        <div class="car-form">
            <h2>Book or Purchase Your Car</h2>
            <form method="POST" action="purchase_car.php">
                <input type="text" name="customer_name" placeholder="Full Name" required>
                <input type="email" name="customer_email" placeholder="Email Address" required>
                <input type="tel" name="customer_phone" placeholder="Phone Number">
                <input type="date" name="purchase_date" min="'.$currentDate.'" required>
                <select name="car_model" required>
                    <option value="">Select Car Model</option>
                    <option value="Toyota Corolla">Toyota Corolla</option>
                    <option value="Honda Civic">Honda Civic</option>
                    <option value="Nissan X-Trail">Nissan X-Trail</option>
                    <option value="Mazda CX-5">Mazda CX-5</option>
                    <option value="Subaru Forester">Subaru Forester</option>
                </select>
                <button type="submit">Confirm Purchase</button>
            </form>
        </div>';
    }
}

// Initialize autoloader
new ClassAutoLoad();
?>

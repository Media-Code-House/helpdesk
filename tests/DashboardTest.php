<?php

use PHPUnit\Framework\TestCase;

class DashboardTest extends TestCase
{
    protected function setUp(): void
    {
        // Inicializa las sesiones de manera segura
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // Limpia las variables de sesión antes de cada prueba
        $_SESSION = [];
    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectIfUserIsNotAdmin()
    {
        // Define el entorno de prueba
        define('TEST_ENVIRONMENT', true);

        // Simula un usuario no administrador
        $_SESSION['rol'] = 'usuario';

        // Captura la excepción generada por la redirección
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Redirect to: /views/incidencias/user_chat.php');

        // Incluye el archivo que se está probando
        include __DIR__ . '/../views/dashboard.php';
    }

    /**
     * @runInSeparateProcess
     */
    public function testAdminAccessDashboard()
    {
        // Define el entorno de prueba
        define('TEST_ENVIRONMENT', true);

        // Simula un usuario administrador
        $_SESSION['rol'] = 'admin';

        // Captura la salida del archivo
        ob_start();
        include __DIR__ . '/../views/dashboard.php';
        $output = ob_get_clean();

        // Verifica que el contenido del dashboard se renderiza correctamente
        $this->assertStringContainsString('<title>Dashboard - Estadísticas de Incidencias</title>', $output);
    }
}

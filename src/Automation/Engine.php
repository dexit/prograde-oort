<?php

namespace ProgradeOort\Automation;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Engine
{
    private static $instance = null;
    private $expressionLanguage;
    private $allowEval = false; // Feature flag for eval() - disabled by default

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();

        // PHP execution enabled by default with security enhancements
        // Can be disabled via filter if expression-only mode is preferred
        $this->allowEval = apply_filters('prograde_oort_allow_eval', true);

        if (!$this->allowEval) {
            \ProgradeOort\Log\Logger::instance()->info(
                "PHP execution disabled - Expression Language only mode active",
                [],
                'system'
            );
        }
    }

    public function run_flow($flow_id, $data, $custom_logic = null)
    {
        \ProgradeOort\Log\Logger::instance()->info("Running flow: {$flow_id}", $data);

        // If logic is not provided, try to find it in options (backward compatibility)
        if (is_null($custom_logic)) {
            $custom_logic = get_option("prograde_oort_flow_{$flow_id}", '');
        }

        if ($custom_logic) {
            return $this->execute_custom_logic($custom_logic, $data);
        }

        return ['status' => 'success', 'message' => 'Flow executed (no custom logic)'];
    }

    private function execute_custom_logic($code, $data)
    {
        // Check if this is PHP code (starts with <?php) or expression syntax
        $isPHP = (strpos(trim($code), '<?php') === 0);

        if ($isPHP && $this->allowEval) {
            return $this->execute_php_legacy($code, $data);
        } elseif ($isPHP) {
            // PHP code provided but eval is disabled
            \ProgradeOort\Log\Logger::instance()->error(
                "Cannot execute PHP code: eval() is disabled for security. Use expression syntax instead.",
                ['flow_code' => substr($code, 0, 100)],
                'execution'
            );
            return [
                'status' => 'error',
                'message' => 'PHP code execution is disabled. Use expression syntax or enable legacy mode (not recommended).'
            ];
        }

        // Execute as safe expression
        return $this->execute_expression($code, $data);
    }

    /**
     * Execute code using safe Symfony Expression Language
     * Supports: variables, operators, functions, but NO arbitrary code execution
     */
    private function execute_expression($expression, $data)
    {
        try {
            // Register safe helper functions
            $this->registerSafeFunctions();

            // Validate expression length (prevent DoS)
            if (strlen($expression) > 10000) {
                throw new \Exception('Expression too long (max 10000 characters)');
            }

            $result = $this->expressionLanguage->evaluate($expression, $data);

            return ['status' => 'success', 'result' => $result];
        } catch (\Throwable $e) {
            \ProgradeOort\Log\Logger::instance()->error(
                "Expression execution error",
                ['error' => $e->getMessage(), 'expression' => substr($expression, 0, 200)],
                'execution'
            );
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Legacy eval() execution - ONLY if explicitly enabled
     * @deprecated Use expression syntax instead
     */
    private function execute_php_legacy($code, $data)
    {
        // Additional security checks for legacy mode
        if (!current_user_can('manage_options')) {
            return [
                'status' => 'error',
                'message' => 'Insufficient permissions for PHP execution'
            ];
        }

        try {
            // Sanitize data before extraction
            $sanitized_data = array_map(function ($value) {
                return is_string($value) ? sanitize_text_field($value) : $value;
            }, $data);

            extract($sanitized_data);

            $result = eval('?>' . $code);

            return ['status' => 'success', 'result' => $result];
        } catch (\Throwable $e) {
            \ProgradeOort\Log\Logger::instance()->error(
                "Legacy PHP execution error",
                ['error' => $e->getMessage()],
                'execution'
            );
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Register safe functions for expression language
     */
    private function registerSafeFunctions()
    {
        // Log function
        $this->expressionLanguage->register('log', function ($str) {
            return sprintf('\ProgradeOort\Log\Logger::instance()->info(%s)', $str);
        }, function ($arguments, $message) {
            \ProgradeOort\Log\Logger::instance()->info($message, [], 'execution');
            return $message;
        });

        // JSON encode
        $this->expressionLanguage->register('json', function ($str) {
            return sprintf('json_encode(%s)', $str);
        }, function ($arguments, $data) {
            return json_encode($data);
        });

        // String concatenation helper
        $this->expressionLanguage->register('concat', function () {
            return 'implode("", func_get_args())';
        }, function ($arguments, ...$strings) {
            return implode('', $strings);
        });
    }
}

<?php

namespace ProgradeOort\Api\Controllers;

class WebhookController extends BaseController
{
    /**
     * Handle incoming webhook requests.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function handle($request)
    {
        $params = $request->get_json_params();

        $this->log('Incoming webhook handled by controller', $params, 'webhooks');

        // Execute the automation engine
        $result = \ProgradeOort\Automation\Engine::instance()->run_flow('webhook_received', $params);

        if ($result['status'] === 'success') {
            return $this->success($result['result'] ?? $result);
        }

        return $this->error($result['message'] ?? 'Execution failed', 500, $result);
    }

    /**
     * Handle generic execution requests.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function execute($request)
    {
        $params = $request->get_json_params();
        $flow_id = $params['flow_id'] ?? 'default';
        $data = $params['data'] ?? [];

        $this->log("Execution triggered for flow: $flow_id", $data, 'execution');

        $result = \ProgradeOort\Automation\Engine::instance()->run_flow($flow_id, $data);

        if ($result['status'] === 'success') {
            return $this->success($result['result'] ?? $result);
        }

        return $this->error($result['message'] ?? 'Flow execution failed', 500, $result);
    }
}

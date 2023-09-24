<?php

namespace WHMCS\Module\Addon\domenytv\Api;

class ResponseController
{

    protected $messages = null;
    protected $statusCode = 200;

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function setStatusCode($code) {
        $this->statusCode = $code;
        return $this;
    }

    public function setMessage($message, $type = 'success')
    {
        $this->messages = [['message' => $message, 'type' => $type]];

        return $this;
    }

    public function setMessages(array $messages, $type = 'success')
    {
        $this->messages = $messages;
        array_walk($this->messages, function(&$item) use ($type) {
            $item = ['message' => $item, 'type' => $type];
        });

        return $this;
    }

    public function addMessage($message, $type = 'success')
    {
        $this->messages[] = ['message' => $message, 'type' => $type];

        return $this;
    }

    public function respondNotFound($message = 'Not Found!')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    public function respondInternalError($message = 'Internal Error!')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    public function respondWithError($message)
    {
        return $this->respond([
                    'status' => $this->getStatusCode(),
                    'error' => [
                        'message' => $message,
                        'status_code' => $this->getStatusCode()
                    ]
                ], $this->getStatusCode());
    }

    public function respond($data = [], $headers = [])
    {
        $response = [
            'status' => $this->getStatusCode(),
            'data' => $data,
        ];

        if ($this->getMessages()) {
            $response['messages'] = $this->getMessages();
        }

        http_response_code($this->getStatusCode());

        echo json_encode($response);
        die();
    }

}

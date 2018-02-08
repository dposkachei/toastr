<?php

namespace Bulk\Toastr;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Session\SessionManager;

class Toastr
{

    /**
     * Added notifications
     *
     * @var array
     */
    protected $notifications = [];

    /**
     * Illuminate Session
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * Toastr config
     *
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    private $allowedTypes = ['error', 'info', 'success', 'warning', 'primary'];
    private $allowedTitles = ['Error', 'Info', 'Success', 'Warning', 'Primary'];

    /**
     * Constructor
     *
     * @param \Illuminate\Session\SessionManager $session
     * @param \Illuminate\Contracts\Config\Repository $config
     *
     * @internal param \Illuminate\Session\SessionManager $session
     */
    public function __construct(SessionManager $session, Repository $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Set default languages title
     *
     * @param $array
     * @return $this
     */
    public function config($array)
    {
        foreach ($this->allowedTypes as $key => $type) {
            if (isset($array['title'][$type])) {
                $this->allowedTitles[$key] = $array['title'][$type];
            }
        }
        return $this;
    }

    /**
     * Remove session
     */
    private function removeSession()
    {
        $this->session->remove('toastr::notifications');
    }

    /**
     * Return first notification to Array
     *
     * @return array
     */
    public function toArray()
    {
        $this->removeSession();

        $notification = array_shift($this->notifications);

        if (empty($notification['options'])) {
            unset($notification['options']);
        }
        return $notification;
    }

    /**
     * Return first notification to Json
     *
     * @return string
     */
    public function toJson()
    {
        $this->removeSession();

        $notification = array_shift($this->notifications);

        if (empty($notification['options'])) {
            unset($notification['options']);
        }
        return json_encode($notification);
    }

    public function options($options)
    {

    }

    /**
     * Render the notifications' script tag
     *
     * @return string
     * @internal param bool $flashed Whether to get the
     *
     */
    public function render()
    {
        if ($this->session->get('toastr::notifications')) {
            return false;
        }
        $notifications = $this->session->get('toastr::notifications');
        if (!$notifications) {
            $notifications = [];
        }

        $output = '<script>';
        $lastConfig = [];
        foreach ($notifications as $notification) {

            $config = $this->config->get('toastr.options');

            if (count($notification['options']) > 0) {
                // Merge user supplied options with default options
                $config = array_merge($config, $notification['options']);
            }

            // Config persists between toasts
            if ($config != $lastConfig) {
                $output .= 'toastr.options = ' . json_encode($config) . ';';   
                $lastConfig = $config;
            }

            // Toastr output
            $output .= 'toastr.' . $notification['type'] . "('" . str_replace("'", "\\'", str_replace(['&lt;', '&gt;'], ['<', '>'], e($notification['message']))) . "'" . (isset($notification['title']) ? ", '" . str_replace("'", "\\'", htmlentities($notification['title'])) . "'" : null) . ');';
        }
        $output .= '</script>';

        return $output;
    }

    /**
     * Add a notification
     *
     * @param string $type Could be error, info, success, or warning.
     * @param string $message The notification's message
     * @param string $title The notification's title
     * @param array $options
     *
     * @return bool Returns whether the notification was successfully added or
     * not.
     */
    private function add($type, $message, $title = null, $options = [])
    {
        if (!in_array($type, $this->allowedTypes)) {
            return false;
        }

        if (is_null($title)) {
            $title = $this->allowedTitles[array_search($type, $this->allowedTypes)];
        }
        $this->notifications[] = [
            'type' => $type,
            'title' => $title,
            'text' => $message,
            'options' => $options
        ];

        return $this->session->flash('toastr::notifications', $this->notifications);
    }

    /**
     * Shortcut for adding an info notification
     *
     * @param string $message The notification's message
     * @param string $title The notification's title
     * @param array $options
     *
     * @return $this
     */
    public function info($message, $title = null, $options = [])
    {
        $this->add('info', $message, $title, $options);

        return $this;
    }

    /**
     * Shortcut for adding an error notification
     *
     * @param string $message The notification's message
     * @param string $title The notification's title
     * @param array $options
     *
     * @return $this
     */
    public function error($message, $title = null, $options = [])
    {
        $this->add('error', $message, $title, $options);

        return $this;
    }

    /**
     * Shortcut for adding a warning notification
     *
     * @param string $message The notification's message
     * @param string $title The notification's title
     * @param array $options
     *
     * @return $this
     */
    public function warning($message, $title = null, $options = [])
    {
        $this->add('warning', $message, $title, $options);

        return $this;
    }

    /**
     * Shortcut for adding a success notification
     *
     * @param string $message The notification's message
     * @param string $title The notification's title
     * @param array $options
     *
     * @return $this
     */
    public function success($message, $title = null, $options = [])
    {
        $this->add('success', $message, $title, $options);

        return $this;
    }

    /**
     * Shortcut for adding a success notification
     *
     * @param string $message The notification's message
     * @param string $title The notification's title
     * @param array $options
     *
     * @return $this
     */
    public function primary($message, $title = null, $options = [])
    {
        $this->add('primary', $message, $title, $options);

        return $this;
    }

    /**
     * Clear all notifications
     *
     * @return $this
     */
    public function clear()
    {
        $this->notifications = [];

        return $this;
    }

}

<?php

namespace Supervisor;

class Api
{
    /**
     * @var string
     */
    protected $url = '';

    /**
     * @var array
     */
    private $headers = ['Content-Type: text/xml'];

    /**
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     */
    public function __construct($host = '127.0.0.1', $port = 9001, $username = '', $password = '')
    {
        $this->url = 'http://' . $host . ':' . $port . '/RPC2';

        if ($username) {
            $this->headers[] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
        }
    }

    /**
     * @param string $method
     * @param array $params
     * @return array
     * @throws \Exception
     */
    function __call($method, array $params = [])
    {
        if (strpos($method, '.') === false) {
            $method = 'supervisor.' . $method;
        }

        $response = \xmlrpc_decode(trim(file_get_contents($this->url, false, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => $this->headers,
                'content' => \xmlrpc_encode_request($method, $params)
            )
        )))));

        if (!$response) {
            throw new ApiException('Invalid response from ' . $this->url);
        }

        if (is_array($response) && xmlrpc_is_fault($response)) {
            throw new ApiException($response['faultString'], $response['faultCode']);
        }

        return $response;
    }

    /**
     * Returns the version of the RPC API used by supervisord
     *
     * This API is versioned separately from Supervisor itself. The API version returned by getAPIVersion only changes
     * when the API changes. Its purpose is to help the client identify with which version of the Supervisor API it is
     * communicating.
     *
     * When writing software that communicates with this API, it is highly recommended that you first test the API
     * version for compatibility before making method calls.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getAPIVersion
     * @return string
     */
    public function getApiVersion()
    {
        return $this->__call('getAPIVersion');
    }

    /**
     * Returns the version of the supervisor package in use by supervisord
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getSupervisorVersion
     * @return string
     */
    public function getSupervisorVersion()
    {
        return $this->__call('getSupervisorVersion');
    }

    /**
     * Returns identifying string of supervisord
     *
     * This method allows the client to identify with which Supervisor instance it is communicating in the case of
     * environments where multiple Supervisors may be running.
     *
     * The identification is a string that must be set in Supervisor’s configuration file. This method simply returns
     * that value back to the client.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getIdentification
     * @return string
     */
    public function getIdentification()
    {
        return $this->__call('getIdentification');
    }

    /**
     * Returns current state of supervisord as a struct
     *
     * This is an internal value maintained by Supervisor that determines what Supervisor believes to be its current
     * operational state.
     *
     * Some method calls can alter the current state of the Supervisor. For example, calling the method
     * supervisor.shutdown() while the station is in the RUNNING state places the Supervisor in the SHUTDOWN state
     * while it is shutting down.
     *
     * The supervisor.getState() method provides a means for the client to check Supervisor’s state, both for
     * informational purposes and to ensure that the methods it intends to call will be permitted.
     *
     * The return value is a struct:
     * {'statecode': 1, 'statename': 'RUNNING'}
     *
     * The possible return values are:
     * Code    Name         Description
     *  2      FATAL        Supervisor has experienced a serious error.
     *  1      RUNNING      Supervisor is working normally.
     *  0      RESTARTING   Supervisor is in the process of restarting.
     *  -1     SHUTDOWN     Supervisor is in the process of shutting down.
     *
     * The FATAL state reports unrecoverable errors, such as internal errors inside Supervisor or system runaway
     * conditions. Once set to FATAL, the Supervisor can never return to any other state without being restarted.
     *
     * In the FATAL state, all future methods except supervisor.shutdown() and supervisor.restart() will automatically
     * fail without being called and the fault FATAL_STATE will be raised.
     *
     * In the SHUTDOWN or RESTARTING states, all method calls are ignored and their possible return values are undefined.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getState
     * @return array
     */
    public function getState()
    {
        return $this->__call('getState');
    }

    /**
     * Returns the PID of supervisord
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getPID
     * @return int
     */
    public function getPid()
    {
        $this->__call('getPID');
    }

    /**
     * Reads length bytes from the main log starting at offset
     *
     * It can either return the entire log, a number of characters from the tail of the log, or a slice of the log
     * specified by the offset and length parameters:
     *
     * Offset           Length      Behavior of readProcessLog
     * Negative         Not Zero    Bad arguments. This will raise the fault BAD_ARGUMENTS.
     * Negative         Zero        This will return the tail of the log, or offset number of characters from the end of
     *                              the log. For example, if offset = -4 and length = 0, then the last four characters
     *                              will be returned from the end of the log.
     * Zero or Positive Negative    Bad arguments. This will raise the fault BAD_ARGUMENTS.
     * Zero or Positive Zero        All characters will be returned from the offset specified.
     * Zero or Positive Positive    A number of characters length will be returned from the offset.
     *
     * If the log is empty and the entire log is requested, an empty string is returned.
     * If either offset or length is out of range, the fault BAD_ARGUMENTS will be returned.
     * If the log cannot be read, this method will raise either the NO_FILE error if the file does not exist or the
     * FAILED error if any other problem was encountered.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.readLog
     * @param int $offset Offset to start reading from.
     * @param int $length Number of bytes to read from the log.
     * @return string Bytes of log
     */
    public function readLog($offset, $length)
    {
        $this->__call('readLog', [$offset, $length]);
    }

    /**
     * Clears the main log
     *
     * If the log cannot be cleared because the log file does not exist, the fault NO_FILE will be raised. If the log
     * cannot be cleared for any other reason, the fault FAILED will be raised.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.clearLog
     * @return bool True on success
     */
    public function clearLog()
    {
        $this->__call('clearLog');
    }

    /**
     * Shuts down the supervisor process
     *
     * This method shuts down the Supervisor daemon. If any processes are running, they are automatically killed without
     * warning.
     *
     * Unlike most other methods, if Supervisor is in the FATAL state, this method will still function.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.shutdown
     * @return bool True on success
     */
    public function shutdown()
    {
        $this->__call('shutdown');
    }

    /**
     * Restarts the supervisor process
     *
     * This method soft restarts the Supervisor daemon. If any processes are running, they are automatically killed
     * without warning. Note that the actual UNIX process for Supervisor cannot restart; only Supervisor’s main program
     * loop. This has the effect of resetting the internal states of Supervisor.
     *
     * Unlike most other methods, if Supervisor is in the FATAL state, this method will still function.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.restart
     * @return bool True on success
     */
    public function restart()
    {
        $this->__call('restart');
    }

    /**
     * Returns info about a process named name
     *
     * The return value is a struct:
     * {
     *      'name': 'process name', // Name of the process
     *      'group': 'group name', // Name of the process’ group
     *      'start': 1200361776, // UNIX timestamp of when the process was started
     *      'stop': 0, // UNIX timestamp of when the process last ended, or 0 if the process has never been stopped.
     *      'now': 1200361812, // UNIX timestamp of the current time, which can be used to calculate process up-time.
     *      'state': 1, // State code
     *      'statename': 'RUNNING', // String description of state
     *      'spawnerr': '', // Description of error that occurred during spawn, or empty string if none.
     *      'exitstatus': 0, // Exit status (errorlevel) of process, or 0 if the process is still running.
     *      'stdout_logfile': '/path/to/stdout-log', // Absolute path and filename to the STDOUT logfile
     *      'stderr_logfile': '/path/to/stderr-log', // Absolute path and filename to the STDOUT logfile
     *      'pid': 1 // UNIX process ID (PID) of the process, or 0 if the process is not running.
     * }
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getProcessInfo
     * @param string $name The name of the process (or group:name)
     * @return array
     */
    public function getProcessInfo($name)
    {
        return $this->__call('getProcessInfo', [$name]);
    }

    /**
     * Returns info about all processes
     *
     * Each element contains a struct, and this struct contains the exact same elements as the struct returned by
     * getProcess. If the process table is empty, an empty array is returned.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getAllProcessInfo
     * @return array
     */
    public function getAllProcessInfo()
    {
        return $this->__call('getAllProcessInfo');
    }

    /**
     * Starts a process
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.startProcess
     * @param string $name Process name (or group:name, or group:*)
     * @param bool $wait Wait for process to be fully started
     * @return bool True on success
     */
    public function startProcess($name, $wait = true)
    {
        $this->__call('startProcess', [$name, $wait]);
    }

    /**
     * Stops a process named by name
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.stopProcess
     * @param string $name The name of the process to stop (or ‘group:name’)
     * @param bool $wait Wait for the process to be fully stopped
     * @return bool True on success
     */
    public function stopProcess($name, $wait = true)
    {
        return $this->__call('stopProcess', [$name, $wait]);
    }

    /**
     * Starts all processes in the group named ‘name’
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.startProcessGroup
     * @param string $name The group name
     * @param bool $wait Wait for each process to be fully started
     * @return array An array of process status info structs
     */
    public function startProcessGroup($name, $wait = true)
    {
        $this->__call('startProcessGroup', [$name, $wait]);
    }

    /**
     * Stops all processes in the process group named ‘name’
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.stopProcessGroup
     * @param string $name The group name
     * @param bool $wait Wait for each process to be fully stopped
     * @return array An array of process status info structs
     */
    public function stopProcessGroup($name, $wait = true)
    {
        $this->__call('stopProcessGroup', [$name, $wait]);
    }

    /**
     * Starts all processes listed in the configuration file
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.startAllProcesses
     * @param bool $wait Wait for process to be fully started
     * @return array An array of process status info structs
     */
    public function startAllProcesses($wait = true)
    {
        $this->__call('startAllProcesses', [$wait]);
    }

    /**
     * Stops all processes in the process list
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.stopAllProcesses
     * @param bool $wait Wait for process to be fully started
     * @return array An array of process status info structs
     */
    public function stopAllProcesses($wait = true)
    {
        $this->__call('stopAllProcesses', [$wait]);
    }

    /**
     * Sends a string of chars to the stdin of the process name.
     *
     * If non-7-bit data is sent (unicode), it is encoded to utf-8 before being sent to the process’ stdin. If chars is
     * not a string or is not unicode, raise INCORRECT_PARAMETERS. If the process is not running, raise NOT_RUNNING. If
     * the process’ stdin cannot accept input (e.g. it was closed by the child process), raise NO_FILE.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.sendProcessStdin
     * @param string $name The process name to send to (or ‘group:name’)
     * @param string $chars The character data to send to the process
     * @return bool True on success
     */
    public function sendProcessStdin($name, $chars)
    {
        $this->__call('sendProcessStdin', [$name, $chars]);
    }

    /**
     * Sends an event that will be received by event listener subprocesses subscribing to the RemoteCommunicationEvent.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.sendRemoteCommEvent
     * @param string $type String for the “type” key in the event header
     * @param string $data Data for the event body
     * @return bool True on success
     */
    public function sendRemoteCommEvent($type, $data)
    {
        $this->__call('sendRemoteCommEvent', [$type, $data]);
    }

    /**
     * Updates the config for a running process from config file.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.addProcessGroup
     * @param string $name Name of process group to add
     * @return bool True on success
     */
    public function addProcessGroup($name)
    {
        $this->__call('addProcessGroup', [$name]);
    }

    /**
     * Removes a stopped process from the active configuration
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.removeProcessGroup
     * @param string $name Name of process group to remove
     * @return bool True on success
     */
    public function removeProcessGroup($name)
    {
        return $this->__call('removeProcessGroup', [$name]);
    }

    /**
     * Reads length bytes from name’s stdout log starting at offset
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.readProcessStdoutLog
     * @param string $name The name of the process (or ‘group:name’)
     * @param int $offset Offset to start reading from
     * @param int $length Number of bytes to read from the log
     * @return string Bytes of log
     */
    public function readProcessStdoutLog($name, $offset, $length)
    {
        $this->__call('readProcessStdoutLog', [$name, $offset, $length]);
    }

    /**
     * Read length bytes from name’s stderr log starting at offset
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.tailProcessStdoutLog
     * @param string $name The name of the process (or ‘group:name’)
     * @param int $offset Offset to start reading from
     * @param int $length Number of bytes to read from the log
     * @return string Bytes of log
     */
    public function readProcessStderrLog($name, $offset, $length)
    {
        $this->__call('readProcessStderrLog', [$name, $offset, $length]);
    }

    /**
     * Provides a more efficient way to tail the (stdout) log than readProcessStdoutLog(). Use readProcessStdoutLog()
     * to read chunks and tailProcessStdoutLog() to tail.
     *
     * Requests (length) bytes from the (name)’s log, starting at (offset). If the total log size is greater than
     * (offset + length), the overflow flag is set and the (offset) is automatically increased to position the buffer
     * at the end of the log. If less than (length) bytes are available, the maximum number of available bytes will be
     * returned. (offset) returned is always the last offset in the log +1.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.tailProcessStdoutLog
     * @param string $name The name of the process (or ‘group:name’)
     * @param int $offset Offset to start reading from
     * @param int $length Maximum number of bytes to return
     * @return array [string bytes, int offset, bool overflow]
     */
    public function tailProcessStdoutLog($name, $offset, $length)
    {
        $this->__call('tailProcessStdoutLog', [$name, $offset, $length]);
    }

    /**
     * Provides a more efficient way to tail the (stderr) log than readProcessStderrLog(). Use readProcessStderrLog() to
     * read chunks and tailProcessStderrLog() to tail.
     *
     * Requests (length) bytes from the (name)’s log, starting at (offset). If the total log size is greater than
     * (offset + length), the overflow flag is set and the (offset) is automatically increased to position the buffer
     * at the end of the log. If less than (length) bytes are available, the maximum number of available bytes will be
     * returned. (offset) returned is always the last offset in the log +1.
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.tailProcessStderrLog
     * @param string $name The name of the process (or ‘group:name’)
     * @param int $offset Offset to start reading from
     * @param int $length Maximum number of bytes to return
     * @return array [string bytes, int offset, bool overflow]
     */
    public function tailProcessStderrLog($name, $offset, $length)
    {
        $this->__call('tailProcessStderrLog', [$name, $offset, $length]);
    }

    /**
     * Clears the stdout and stderr logs for the named process and reopen them
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.clearProcessLogs
     * @param string $name The name of the process (or ‘group:name’)
     * @return array An array of process status info structs
     */
    public function clearProcessLogs($name)
    {
        $this->__call('clearProcessLogs', $name);
    }

    /**
     * Clears all process log files
     *
     * @see http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.clearAllProcessLogs
     * @return array An array of process status info structs
     */
    public function clearAllProcessLogs()
    {
        $this->__call('clearAllProcessLogs');
    }

    /**
     * Returns an array listing the available method names
     *
     * @see http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.listMethods
     * @return string An array of method names available (strings)
     */
    public function listMethods()
    {
        $this->__call('system.listMethods');
    }

    /**
     * Returns a string showing the method’s documentation
     *
     * http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.methodHelp
     * @param string $name
     * @return string The documentation for the method name
     */
    public function methodHelp($name)
    {
        $this->__call('system.methodHelp', ['supervisor.' . $name]);
    }

    /**
     * Returns an array describing the method signature in the form [rtype, ptype, ptype...] where rtype is the return
     * data type of the method, and ptypes are the parameter data types that the method accepts in method argument order.
     *
     * @see http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.methodSignature
     * @param string $name The name of the method
     * @return array
     */
    public function methodSignature($name)
    {
        $this->__call('system.methodSignature', ['supervisor.' . $name]);
    }

    /**
     * Processes an array of calls, and return an array of results. Calls should be structs of the form
     * {‘methodName’: string, ‘params’: array}. Each result will either be a single-item array containg the result value,
     * or a struct of the form {‘faultCode’: int, ‘faultString’: string}. This is useful when you need to make lots of
     * small calls without lots of round trips.
     *
     * @see http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.multicall
     * @param array $calls An array of call requests
     * @return array An array of results
     */
    public function multicall(array $calls)
    {
        $this->__call('system.multicall', [$calls]);
    }

}

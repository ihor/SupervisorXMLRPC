Supervisor
==========

PHP XML RPC Client for [Supervisor](http://supervisord.org)

Installation
------------
Define the following requirement in your composer.json file:
```
"require": {
    "ihor/supervisor-xml-rpc": "~0.2"
}
```

It also requires [PHP XML-RPC extension](http://php.net/manual/en/book.xmlrpc.php). The version is marked as dev because I haven't heard any feedback from other users.

Usage
-----

```php
// Create Supervisor API instance
$api = new \Supervisor\Api('127.0.0.1', 9001 /* username, password */);

// Call Supervisor API
$api->getApiVersion();

// That's all!
```

Reference
---------

##### getApiVersion()

Returns the version of the RPC API used by supervisord

This API is versioned separately from Supervisor itself. The API version returned by getAPIVersion only changes when the API changes. Its purpose is to help the client identify with which version of the Supervisor API it is communicating.

When writing software that communicates with this API, it is highly recommended that you first test the API version for compatibility before making method calls.

[getAPIVersion](http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getAPIVersion)


##### getSupervisorVersion()

Returns the version of the supervisor package in use by supervisord

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getSupervisorVersion


##### getIdentification()

Returns identifying string of supervisord

This method allows the client to identify with which Supervisor instance it is communicating in the case of environments where multiple Supervisors may be running.

The identification is a string that must be set in Supervisor’s configuration file. This method simply returns that value back to the client.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getIdentification


##### getState()

Returns current state of supervisord as a struct

This is an internal value maintained by Supervisor that determines what Supervisor believes to be its current operational state.

Some method calls can alter the current state of the Supervisor. For example, calling the method supervisor.shutdown() while the station is in the RUNNING state places the Supervisor in the SHUTDOWN state while it is shutting down.

The supervisor.getState() method provides a means for the client to check Supervisor’s state, both for informational purposes and to ensure that the methods it intends to call will be permitted.

The return value is a struct:

The possible return values are:

Code | Name       | Description
-----|------------|-----------------------------------------------
 2   | FATAL      | Supervisor has experienced a serious error.
 1   | RUNNING    | Supervisor is working normally.
 0   | RESTARTING | Supervisor is in the process of restarting.
 -1  | SHUTDOWN   | Supervisor is in the process of shutting down.

The FATAL state reports unrecoverable errors, such as internal errors inside Supervisor or system runaway conditions. Once set to FATAL, the Supervisor can never return to any other state without being restarted.

In the FATAL state, all future methods except supervisor.shutdown() and supervisor.restart() will automatically fail without being called and the fault FATAL_STATE will be raised.

In the SHUTDOWN or RESTARTING states, all method calls are ignored and their possible return values are undefined.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getState


##### getPid()

Returns the PID of supervisord

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getPID


##### readLog($offset, $length)

Reads length bytes from the main log starting at offset

It can either return the entire log, a number of characters from the tail of the log, or a slice of the log specified by the offset and length parameters:

Offset           | Length    |  Behavior of readProcessLog
-----------------|-----------|-----------------------------------------------------------------
Negative         | Not Zero  |  Bad arguments. This will raise the fault BAD_ARGUMENTS.
Negative         | Zero      |  This will return the tail of the log, or offset number of characters from the end of the log. For example, if offset = -4 and length = 0, then the last four characters will be returned from the end of the log.
Zero or Positive | Negative  |  Bad arguments. This will raise the fault BAD_ARGUMENTS.
Zero or Positive | Zero      |  All characters will be returned from the offset specified.
Zero or Positive | Positive  |  A number of characters length will be returned from the offset.

If the log is empty and the entire log is requested, an empty string is returned.
If either offset or length is out of range, the fault BAD_ARGUMENTS will be returned.
If the log cannot be read, this method will raise either the NO_FILE error if the file does not exist or the FAILED error if any other problem was encountered.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.readLog


##### clearLog()

Clears the main log

If the log cannot be cleared because the log file does not exist, the fault NO_FILE will be raised. If the log cannot be cleared for any other reason, the fault FAILED will be raised.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.clearLog


##### shutdown()

Shuts down the supervisor process

This method shuts down the Supervisor daemon. If any processes are running, they are automatically killed without warning.
Unlike most other methods, if Supervisor is in the FATAL state, this method will still function.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.shutdown


##### restart()

Restarts the supervisor process

This method soft restarts the Supervisor daemon. If any processes are running, they are automatically killed without warning. Note that the actual UNIX process for Supervisor cannot restart; only Supervisor’s main program loop. This has the effect of resetting the internal states of Supervisor.

Unlike most other methods, if Supervisor is in the FATAL state, this method will still function.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.restart


##### getProcessInfo($name)

Returns info about a process named name

The return value is a struct:
```
{'name':           'process name',
 'group':          'group name',
 'description':    'pid 18806, uptime 0:03:12'
 'start':          1200361776,
 'stop':           0,
 'now':            1200361812,
 'state':          1,
 'statename':      'RUNNING',
 'spawnerr':       '',
 'exitstatus':     0,
 'logfile':        '/path/to/stdout-log', # deprecated, b/c only
 'stdout_logfile': '/path/to/stdout-log',
 'stderr_logfile': '/path/to/stderr-log',
 'pid':            1}
```

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getProcessInfo


##### getAllProcessInfo()

Returns info about all processes

Each element contains a struct, and this struct contains the exact same elements as the struct returned by getProcess. If the process table is empty, an empty array is returned.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.getAllProcessInfo


##### startProcess($name, $wait = true)

Starts a process

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.startProcess


##### stopProcess($name, $wait = true)

Stops a process named by name

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.stopProcess


##### startProcessGroup($name, $wait = true)

Starts all processes in the group named by name

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.startProcessGroup


##### stopProcessGroup($name, $wait = true)

Stops all processes in the process group named by name

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.stopProcessGroup


##### startAllProcesses($wait = true)

Starts all processes listed in the configuration file

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.startAllProcesses


##### stopAllProcesses($wait = true)

Stops all processes in the process list

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.stopAllProcesses


##### sendProcessStdin($name, $chars)

Sends a string of chars to the stdin of the process name.

If non-7-bit data is sent (unicode), it is encoded to utf-8 before being sent to the process’ stdin. If chars i not a string or is not unicode, raise INCORRECT_PARAMETERS. If the process is not running, raise NOT_RUNNING. If the process’ stdin cannot accept input (e.g. it was closed by the child process), raise NO_FILE.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.sendProcessStdin


##### sendRemoteCommEvent($type, $data)

Sends an event that will be received by event listener subprocesses subscribing to the RemoteCommunicationEvent.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.sendRemoteCommEvent


##### addProcessGroup($name)

Updates the config for a running process from config file.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.addProcessGroup


##### removeProcessGroup($name)

Removes a stopped process from the active configuration

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.removeProcessGroup


##### readProcessStdoutLog($name, $offset, $length)

Reads length bytes from name’s stdout log starting at offset

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.readProcessStdoutLog


##### readProcessStderrLog($name, $offset, $length)

Read length bytes from name’s stderr log starting at offset

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.tailProcessStderrLog


##### tailProcessStdoutLog($name, $offset, $length)

Provides a more efficient way to tail the (stdout) log than readProcessStdoutLog(). Use readProcessStdoutLog() to read chunks and tailProcessStdoutLog() to tail.

Requests (length) bytes from the (name)’s log, starting at (offset). If the total log size is greater than (offset + length), the overflow flag is set and the (offset) is automatically increased to position the buffer at the end of the log. If less than (length) bytes are available, the maximum number of available bytes will be returned. (offset) returned is always the last offset in the log +1.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.tailProcessStdoutLog


##### tailProcessStderrLog($name, $offset, $length)

Provides a more efficient way to tail the (stderr) log than readProcessStderrLog(). Use readProcessStderrLog() to read chunks and tailProcessStderrLog() to tail.

Requests (length) bytes from the (name)’s log, starting at (offset). If the total log size is greater than (offset + length), the overflow flag is set and the (offset) is automatically increased to position the buffer at the end of the log. If less than (length) bytes are available, the maximum number of available bytes will be returned. (offset) returned is always the last offset in the log +1.

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.tailProcessStderrLog


##### clearProcessLogs($name)

Clears the stdout and stderr logs for the named process and reopen them

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.clearProcessLogs


##### clearAllProcessLogs()

Clears all process log files

http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.clearAllProcessLogs


##### listMethods()

Returns an array listing the available method names

http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.listMethods


##### methodHelp($name)

Returns a string showing the method’s documentation

http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.methodHelp


##### methodSignature($name)

Returns an array describing the method signature in the form [rtype, ptype, ptype...] where rtype is the return data type of the method, and ptypes are the parameter data types that the method accepts in method argument order.

http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.methodSignature


##### multicall(array $calls)

Processes an array of calls, and return an array of results. Calls should be structs of the form. Each result will either be a single-item array containg the result value, or a struct of the form . This is useful when you need to make lots of small calls without lots of round trips.

http://supervisord.org/api.html#supervisor.xmlrpc.SystemNamespaceRPCInterface.multicall

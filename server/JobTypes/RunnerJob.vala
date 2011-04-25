public class RunnerJob : Job
{
    // this is for permanent running jobs
    public bool is_running { get; set; default = false; }

    // AsyncJobs can run indefinetly, but we need to track their failures
    private int start_attempts = 0; 
    
    public RunnerJob ()
    {
        jobtype = JobTypes.RUNNERJOB;
    }

    public RunnerJob.from_name(string n)
    {
        name = n;
        run_at = "*";
        
        load();
    }
    
    public override bool run ()
    {
        /** TODO:
            - run for 24 hours as async
            - then terminate so it can be rescheduled
            - job needs to regularly "heartbeat", if it doesnt, terminate
        **/
        
        if ( is_running || run_at == "-" || start_attempts > 10 )
            return false;

        debug ("RunnerJob.run(): %s", name);

        start_attempts ++;
        is_running = true;

        return true;
    }
}

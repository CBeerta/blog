using Gee;

public class Cron : GLib.Object
{
    private Config config;
    
    private Gee.HashMap<string, Job> joblist;
    
    private Gee.HashMap<string, CronJob> cronjobs;
    private Gee.HashMap<string, RunnerJob> runnerjobs;
    private Gee.HashMap<string, TriggerJob> triggerjobs;
    
    private InternalJob internaljob;
    
    public Cron ()
    {
        // Load Config
        config = new Config();
        config.load();   
        
        // Load initial joblist
        joblist = config.load_jobs_initial();
        
        cronjobs = new Gee.HashMap<string, CronJob> ();
        runnerjobs = new Gee.HashMap<string, RunnerJob> ();
        triggerjobs = new Gee.HashMap<string, TriggerJob> ();
        
        internaljob = new InternalJob ();
        
        foreach (string name in joblist.keys)
        {
            debug (name);
            switch (joblist.get(name).jobtype)
            {
                case JobTypes.CRONJOB:
                    var cronjob = new CronJob.from_name(name);
                    cronjobs.set(name, cronjob);
                    break;
            
                case JobTypes.RUNNERJOB:
                    var runnerjob = new RunnerJob.from_name(name);
                    runnerjobs.set(name, runnerjob);
                    break;

                case JobTypes.TRIGGERJOB:
                    var triggerjob = new TriggerJob.from_name(name);
                    triggerjobs.set(name, triggerjob);
                    break;

                default:
                    error ("This can't happen: Unknown JobType encountered.");
            }
        }
    }
    
    private bool negotiate (Job j)
    {
        // TODO this would negotiate amongst its neighbours who would run this job
        //      for now, it just always says "yes, i will run it"
        return true;
    }   
    
    private bool wakeup ()
    {
        foreach (string name in joblist.keys)
        {
            if ( !negotiate(joblist.get(name)) ) continue;

            switch (joblist.get(name).jobtype)
            {
                case (JobTypes.CRONJOB):
                    cronjobs.get(name).run();
                    break;
                    
                case (JobTypes.RUNNERJOB):  
                    runnerjobs.get(name).run();
                    break;
                    
                case (JobTypes.TRIGGERJOB):  
                    if ( (triggerjobs.get(name).run()) );
                    /* FIXME: this coredumps?!
                    {
                        triggerjobs.unset(name);
                        joblist.unset(name);
                    }
                    */
                    break;
                    
                default:
                    error ("This can't happen: Unknown JobType encountered.");
            }
        }
        
        internaljob.run();
        return true;
    }
    
    public int run ()
    {
        Timeout.add(100, wakeup);
        main_loop.run();
        return 0;
    }
}

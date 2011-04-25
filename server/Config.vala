public class Config : GLib.Object
{
    public Config ()
    {

    }
    
    public void load ()
    {
        debug ("config.load()");
        
    }

    public Gee.HashMap<string, Job> load_jobs_initial ()
    {
        // TODO this should read jobnames from the db, and init them
        //      loading the additional info should be done inside the Job class
        var joblist = new Gee.HashMap<string, Job> ();

        // generate some jobs for us        
        for ( var i = 1 ; i <= 5 ; i++)
        {
            var job = new Job.from_name("org.acmecorp.cronjob." + i.to_string());
            job.jobtype = JobTypes.CRONJOB;
            joblist.set(job.name, job);
        }

        var job = new Job.from_name("org.acmecorp.cronjob.speshul");
        job.jobtype = JobTypes.CRONJOB;
        joblist.set(job.name, job);
        
        var ajob = new Job.from_name("org.acmecorp.runnerjob.1");
        ajob.jobtype = JobTypes.RUNNERJOB;
        joblist.set(ajob.name, ajob);

        var tjob = new Job.from_name("org.acmecorp.triggerjob");
        tjob.jobtype = JobTypes.TRIGGERJOB;
        joblist.set(tjob.name, tjob);
        
        return joblist;
    }
    
    public bool add_job (Job job)
    {
        // TODO add a job to our local list and the db
        //      also distribute it to our neighbours
        
        return true;
    }

}

using Posix;

public static enum JobTypes { NONE, CRONJOB, RUNNERJOB, TRIGGERJOB, INTERNALJOB }

public class Job : GLib.Object
{
    public JobTypes jobtype { get; set; default = JobTypes.NONE; } // FIXME set should be protected
    
    public string name { get; protected set; }
    public string run_at { get; protected set; default = "-";}
    public string author { get; protected set; }
    public string title { get; protected set; }
    public string description { get; protected set; }

    public string[] notification_list { get; protected set; }

    protected bool is_loaded { get; protected set; default = false; }
    protected TimeZone tz { get; protected set; }

    //public Job (string name)
    public Job ()
    {
        name = "Not Properly Setup";
        
        tz = new TimeZone.local();
    }

    public Job.from_name(string n)
    {
        /** TODO:
            - load most barebones info:
                id
                name
                run_at
                jobtype
                
            the rest will by done by the childclass loader
        **/
        name = n;
        load(false);
    }
    
    public virtual bool load (bool full = true)
    {
        return true;
    }

    public virtual bool run ()
    {
        error ("Parent Tried Running me as Job %s", name);
    }
    
    public void disable ()
    {
        this.run_at = "-";
    }
}

public class TriggerJob : Job
{
    private bool has_run { get; private set; default = false; }

    public TriggerJob()
    {
        jobtype = JobTypes.TRIGGERJOB;
    }

    public TriggerJob.from_name(string n)
    {
        name = n;
        run_at = "+";
        
        load();
    }
    
    public override bool run ()
    {
        /** TODO:
            - Triggerjobs should only run once, and then destroy themselves
            - Don't need to store these in the db, we live with the fact that theyre volatile
        **/
        
        // Our caller should remove me from its list, but to be sure, we disable ourselves aswell
        if ( has_run == true  || run_at == "-")
            return false;

        debug ("TriggerJob.run(): %s", name);
        
        has_run = true;
        return true;
    }
}

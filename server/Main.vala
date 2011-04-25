using GLib;

MainLoop main_loop;

int main(string []args)
{
    var cron = new Cron();
    main_loop = new MainLoop();

    return cron.run();
}

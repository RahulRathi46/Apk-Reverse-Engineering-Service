import subprocess
import os , shutil
import threading
from time import sleep

from celery.task import task


@task(name="work")
def work(name, url):
    # CMD
    dir = os.path.dirname(os.path.abspath(__file__))
    work_cmd = "sh " + dir + "/tools/apktool.sh " + " d " + dir + "/static/holder/store/" + url \
               + " -o " + dir + "/static/holder/space/" + name + "/Res/"

    # Execute  job with multiple lines on stdout:
    p = subprocess.Popen(
        work_cmd
        , shell=True
        , stdout=subprocess.PIPE
        ,stderr=subprocess.PIPE
    )

    work_cmd = "sh " + dir + "/tools/bin/jadx -r -j 1 " + " -d " + dir + "/static/holder/space/" + name + "/JavaCode/" \
               + " " + dir + "/static/holder/store/" + url

    q = subprocess.Popen(
        work_cmd
        , shell=True
        , stdout=subprocess.PIPE
        , stderr=subprocess.PIPE
    )

    p_result = []
    q_result = []

    while p.stdout is not None and q.stdout is not None:

        # Update spinner on one step:


        p_line = p.stdout.readline()
        p_line = p_line.decode('UTF-8').rstrip('\r')
        print(p_line)

        p_result.append(p_line)
        print("\n")
        p.stdout.flush()

        q_line = q.stdout.readline()
        q_line = q_line.decode('UTF-8').rstrip('\r')
        print(q_line)
        q_result.append(q_line)

        print("\n")
        q.stdout.flush()

        # When no lines appears:
        if not p_line and not q_line:
            break

    # Show finish message, it also useful because bar cannot start new line on console, why?
    print("Finish:")
    shutil.make_archive(dir + "/static/holder/space/" + name  , 'zip' , dir + "/static/holder/space/" + name)
    return 0

def close(file_loc,delay):
    sleep(delay)
    print("------------------------------" + file_loc)
    os.remove(os.path.dirname(os.path.abspath(__file__)) + "/static/holder/store/" + file_loc.split(".zip")[0])
    os.remove(os.path.dirname(os.path.abspath(__file__)) + "/static/holder/space/" + file_loc)
    shutil.rmtree(os.path.dirname(os.path.abspath(__file__)) + "/static/holder/space/" + file_loc.split(".zip")[0])



# imports
import datetime
import json, os, shutil, subprocess, threading, time, zipfile, requests, multiprocessing
from os import system
from urllib import request

# from googleplay_api import googleplay

# Commands for processing
prc_cmds = {
    'apktool': 'sh worker_tools/apktool.sh d -o ',
    'apktool-output': 'worker_tmp/',
    'apktool-dir': '/apktool/',

    'jadxtool': 'sh worker_tools/bin/jadx -d ',
    'jadxtool-output': 'worker_tmp/',
    'jadxtool-dir': '/jadx/'}

# Worker_Type
Worker_Type = "python"

# Worker Class
processor_2_batch = 0

# thread lock service
threadLock = threading.Lock()

# Worker url
post_result_url = 'https://apkdecompile.com/post_result'
post_stats = 'https://apkdecompile.com/worker'
getqueue = "https://apkdecompile.com/queue"
synced = "https://apkdecompile.com/synced"

# Do not remove
# separator used by search.py, categories.py, ...
SEPARATOR = ";"

LANG            = "en_IN" # can be en_US, fr_FR, ...
ANDROID_ID      = "20013fea6bcc820c"
GOOGLE_LOGIN    = "seveneightbyte@gmail.com"
GOOGLE_PASSWORD = "qwerty@1234"
AUTH_TOKEN      = None # "yyyyyyyyy"


# make zip for post
# credits stackoverflow
def zip_make(src, dst):
    zf = zipfile.ZipFile("%s.zip" % (dst), "w", zipfile.ZIP_DEFLATED)
    abs_src = os.path.abspath(src)
    for dirname, subdirs, files in os.walk(src):
        for filename in files:
            absname = os.path.abspath(os.path.join(dirname, filename))
            arcname = absname[len(abs_src) + 1:]
            zf.write(absname, arcname)
    zf.close()
    print("######################################### Zip done ########################################")


def dowload_from_play():

    global GOOGLE_LOGIN, GOOGLE_PASSWORD, AUTH_TOKEN

    packagename = "com.cloudflare.onedotonedotonedotone"
    filename = packagename + ".apk"


    # Connect
    api = googleplay.GooglePlayAPI(ANDROID_ID)
    api.login(GOOGLE_LOGIN, GOOGLE_PASSWORD, AUTH_TOKEN)

    # Get the version code and the offer type from the app details
    m = api.details(packagename)
    doc = m.docV2
    vc = doc.details.appDetails.versionCode
    ot = doc.offer[0].offerType

    # Download
    data = api.download(packagename, vc, ot)
    open(filename, "wb").write(data)
    print("Done")


# make new process and process batch taks
def worker_cmd(i):
    global Worker_Type

    if Worker_Type == "python":
        cmd = ''
    else:
        cmd = 'php process_main.php "'

    # tools selction and cmds
    if i['TOOL'] == 'both':
        cmd = cmd + prc_cmds['apktool'] + prc_cmds['apktool-output'] + i['FILENAME'][:-4] \
              + prc_cmds['apktool-dir'] + " " + prc_cmds['apktool-output'] + i['FILENAME'] + "&& " + \
              prc_cmds['jadxtool'] + prc_cmds['jadxtool-output'] + i['FILENAME'][:-4] + \
              prc_cmds['jadxtool-dir'] + " " + prc_cmds['jadxtool-output'] + i['FILENAME']
    elif i['TOOL'] == 'apktool':
        cmd = cmd + prc_cmds['apktool'] + prc_cmds['apktool-output'] + i['FILENAME'][:-4] + prc_cmds['apktool-dir'] \
              + " " + prc_cmds['apktool-output'] + i['FILENAME']
    elif i['TOOL'] == 'jadx':
        cmd = cmd + prc_cmds['jadxtool'] + prc_cmds['jadxtool-output'] + i['FILENAME'][:-4] + \
              prc_cmds['jadxtool-dir'] + " " + prc_cmds['jadxtool-output'] + i['FILENAME']
    else:
        # json error
        pass

    if Worker_Type == "python":
        cmd = cmd
    else:
        cmd = cmd + '"'

    return cmd


def do_work_cpu(data, batch_name):
    # get all global variables
    global prc_cmds, post_stats

    # Queue
    print(data)

    # date
    d = datetime.datetime.now()

    # stats post data holders
    date_start = str(d.day) + "-" + str(d.month) + "-" + str(d.year)
    time_start = str(datetime.datetime.now().time())
    date_end = ''
    time_end = ''
    worker_class = batch_name
    batch_size = len(data)
    batch_size_done = 0

    for i in data:
        # Queue item
        print(batch_name + " : [ " + ''.join((e + ": " + i[e] + " , ") for e in i) + " ]")

        # get file
        cmd = "wget -P " + "worker_tmp/ '" + i['FILE_DOWNLOAD_URL'] + "'"
        df = os.system(cmd)
        if df == 0:
            cmd = worker_cmd(i)

            p = subprocess.Popen(
                cmd
                , shell=True
                , stdout=subprocess.PIPE
                , stderr=subprocess.PIPE
            )

            while p.stdout is not None:
                # read stdout and block untill complete
                p_line = p.stdout.readline()
                p.stdout.flush()

                # When no lines appears:
                if not p_line:
                    break

            # make zip
            zip_make('worker_tmp/' + i['FILENAME'][:-4], 'worker_tmp/' + i['FILENAME'][:-4])
            files = {'file': open('worker_tmp/' + i['FILENAME'][:-4] + '.zip', 'rb'), 'UID': i['UID']}
            params = {'UID': i['UID']}

            r = requests.post(
                post_result_url,
                files=files,
                data=params
            )

            print("--------------------------------------")
            print("POST HTTP CODE : " + str(r.status_code) + " Contents : " + r.text)
            print("--------------------------------------")

            if r.status_code == 200:
                batch_size_done = batch_size_done + 1
            else:
                for it in i:
                    log = log + it
                system("echo '" + date_start + log + "' >> loger.log")

            shutil.rmtree('worker_tmp/' + i['FILENAME'][:-4])
            os.remove('worker_tmp/' + i['FILENAME'][:-4] + '.zip')
            os.remove('worker_tmp/' + i['FILENAME'])

    d = datetime.datetime.now()
    time_end = str(datetime.datetime.now().time())
    date_end = str(d.day) + "-" + str(d.month) + "-" + str(d.year)
    post_stats_results = {"date_start": date_start, "date_end": date_end, "time_start": time_start,
                          "time_end": time_end,
                          "worker_class": worker_class, "batch_size": batch_size, "batch_size_done": batch_size_done}
    r = requests.post(post_stats, data=post_stats_results)
    print("--------------------------------------")
    print("POST-STATS HTTP CODE : " + str(r.status_code) + " Contents : " + r.text)
    print("--------------------------------------")


def do_work(i, batch_name):
    # get all global variables
    global prc_cmds, post_stats

    # date
    d = datetime.datetime.now()

    # stats post data holders
    date_start = str(d.day) + "-" + str(d.month) + "-" + str(d.year)
    time_start = str(datetime.datetime.now().time())
    date_end = ''
    time_end = ''
    worker_class = batch_name
    batch_size = len(i)
    batch_size_done = 0

    # Queue item
    print(batch_name + " : [ " + ''.join((e + ": " + i[e] + " , ") for e in i) + " ]")

    # get file
    cmd = "wget -P " + "worker_tmp/ '" + i['FILE_DOWNLOAD_URL'] + "'"
    df = os.system(cmd)
    if df == 0:
        cmd = worker_cmd(i)

        p = subprocess.Popen(
            cmd
            , shell=True
            , stdout=subprocess.PIPE
            , stderr=subprocess.PIPE
        )

        while p.stdout is not None:
            # read stdout and block untill complete
            p_line = p.stdout.readline()
            p.stdout.flush()

            # When no lines appears:
            if not p_line:
                break

        # make zip
        zip_make('worker_tmp/' + i['FILENAME'][:-4], 'worker_tmp/' + i['FILENAME'][:-4])
        files = {'file': open('worker_tmp/' + i['FILENAME'][:-4] + '.zip', 'rb'), 'UID': i['UID']}
        params = {'UID': i['UID']}

        r = requests.post(
            post_result_url,
            files=files,
            data=params
        )

        print("--------------------------------------")
        print("POST HTTP CODE : " + str(r.status_code) + " Contents : " + r.text)
        print("--------------------------------------")

        if r.status_code == 200:
            batch_size_done = batch_size_done + 1
        else:
            for it in i:
                log = log + it
            system("echo '" + date_start + log + "' >> loger.log")

        shutil.rmtree('worker_tmp/' + i['FILENAME'][:-4])
        os.remove('worker_tmp/' + i['FILENAME'][:-4] + '.zip')
        os.remove('worker_tmp/' + i['FILENAME'])

    d = datetime.datetime.now()
    time_end = str(datetime.datetime.now().time())
    date_end = str(d.day) + "-" + str(d.month) + "-" + str(d.year)
    post_stats_results = {"date_start": date_start, "date_end": date_end, "time_start": time_start,
                          "time_end": time_end,
                          "worker_class": worker_class, "batch_size": batch_size, "batch_size_done": batch_size_done}
    r = requests.post(post_stats, data=post_stats_results)
    print("--------------------------------------")
    print("POST-STATS HTTP CODE : " + str(r.status_code) + " Contents : " + r.text)
    print("--------------------------------------")


def work_manager_1(data):
    global processor_2_batch
    p = multiprocessing.Process(target=do_work_cpu, args=(data, "batch-worker-2"))
    p.start()
    p.join()
    threadLock.acquire()
    processor_2_batch = processor_2_batch - 1
    threadLock.release()

def main():
    global Worker_Type,processor_2_batch

    Worker_Type = "php" # we have two variants of workers 1. python and 2. php ; use { 'php' , 'python' } keys to change worker
    # php is faster and needs less res then python but php is less reliable where python is more reliable and slower then php.
    # different work needs differnt types of res use what best avl. to you.
    
    while (1):
        try:
            json_url = request.urlopen(getqueue)
            data = json.loads(json_url.read())
            if (processor_2_batch < 3 and processor_2_batch >= 0 and len(data) >= 1):
                print("Data Recived len : " + str(len(data)))
                for i in data:
                    params = {'UID': i['UID']}

                    r = requests.post(
                        synced,
                        data=params
                    )

                    print("--------------------------------------")
                    print("SYNC HTTP CODE : " + str(r.status_code) + " Contents : " + r.text)
                    print("--------------------------------------")

                    time.sleep(.5)
                threadLock.acquire()
                processor_2_batch = processor_2_batch + 1
                threading.Thread(target=work_manager_1, args=(data,)).start()
                threadLock.release()
            else:
                print("Data Recived len : " + str(len(data)) + " [No Task] [ Wait Extra Added : 5 sec ]")
                time.sleep(5)
            time.sleep(15)
        except Exception as e:
            print(e)
            time.sleep(20)

if __name__ == '__main__':
    main()

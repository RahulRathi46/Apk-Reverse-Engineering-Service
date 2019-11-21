import json
from django.core.files.storage import FileSystemStorage
from django.shortcuts import render, redirect
from django.http import HttpResponse, HttpResponseRedirect
from . import tasks

def index(request):
    return render(request,"Site/index.html",{})

def schedule(request):
        myfile = request.FILES['file']
        if ".apk" in myfile.name:
            fs = FileSystemStorage("Site/static/holder/store/")
            request.session['has_task'] = True
            filename = fs.save(myfile.name.replace(" ", "_") , myfile)

            (tasks.work).delay(filename , fs.url(filename))

            request.session['id'] = 1
            request.session['has_id'] = True
            request.session['task_run'] = True
            request.session['file_name'] = filename + '.zip'
            return render(request , 'Site/process.html' , {'URL': "/state/"+str(1)})
        else:
            return HttpResponseRedirect('/')

def progress(request,id):
    state = 'error'
    result = ""
    response_data = {
        'state': state,
        'result': result,
    }
    return HttpResponse(json.dumps(response_data), content_type='application/json')

def complete(request):
    if request.session.get('has_task', False) & request.session.get('has_id', False):
        request.session['has_task'] = False
        request.session['has_id'] = False
        request.session['task_run'] = False
        file = request.session.get('file_name', False)
        format = {'close' : '/close/' + file , 'file_url':  '/static/holder/space/' + file}
        return render(request, "Site/complete.html", format)
    else:
        return HttpResponseRedirect('/')
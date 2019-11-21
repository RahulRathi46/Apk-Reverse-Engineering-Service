from django.conf.urls import url
from . import views

urlpatterns = [
    url(r'^$' , views.index, name='index' ),
    url(r'^task$' , views.schedule, name='task' ),
    url(r'^state/(?P<id>[0-9])/$', views.progress, name='status'),
    url(r'^complete$' , views.complete, name='complete' )
]
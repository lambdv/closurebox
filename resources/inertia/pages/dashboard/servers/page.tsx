import { PlaceholderPattern } from '@/components/cn/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { useState } from 'react'
import { router } from '@inertiajs/react'
import { useForm } from '@inertiajs/react'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Servers',
        href: '/servers',
    },
];

/**
 * UI for managing server products
 * lets you:
 * create new ec2 instance pdocuts
 * view ec2 instance products you own in a table (with actions)
 * for each ec2 instance product, you can:
 * - view details
 * - start/stop/terminate
 * - edit settings
 * - delete
 */
export default function Servers({}) {
  const props = usePage().props

  const { data, setData, post, processing, errors } = useForm({
    name: '',
  })

  return (
      <AppLayout breadcrumbs={breadcrumbs}>
          <Head title="Servers" />
          <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

          {/* {errors.errors && <div className="alert alert-danger bg-red-700">{errors.errors}</div>}

          {props.flash.success && <div className="alert alert-success bg-green-700">{props.flash.success}</div>} */}

          <div className="block">
                <h1>Servers</h1>
                <form onSubmit={(e) => {
                  e.preventDefault();
                  //console.log(data);
                  post('/servers/create')
                }} className="grid col-1 gap-4">
                  <div className="form-group">
                    <label htmlFor="name">Name</label>
                    <input type="text" name="name" id="name" className="form-control" placeholder="Enter server name" required value={data.name} onChange={(e) => setData('name', e.target.value)} />
                  </div>

                  <button type="submit" className="btn btn-primary">Create Server</button>
                </form>
              </div>

              <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                <table className="table">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Status</th>
                      <th>Created At</th>
                      <th>Updated At</th>
                    </tr>
                  </thead>
                <tbody>
                  {props.servers.map((server: any) => (
                    <tr key={server.id}>
                      <td>{server.name}</td>
                      <td>{server.status}</td>
                      <td>{server.created_at}</td>
                      <td>{server.updated_at}</td>
                    </tr>
                  ))}
                </tbody>
              </table> 
              </div>
                          
          </div>
      </AppLayout>
    );
}

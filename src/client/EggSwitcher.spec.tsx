import { afterEach, expect, test, vi } from 'vitest';
import { cleanup, render, screen } from '@testing-library/react';
import { serverResourcesQueryOptions, type StartupFormSlotData } from '@pterodactyl/sdk';
import { createExtensionTestHost, createTestServer } from '@pterodactyl/sdk/testing';
import EggSwitcher from './EggSwitcher';

vi.mock('./api', async (importOriginal) => ({
    ...(await importOriginal<typeof import('./api')>()),
    getOptions: vi.fn().mockResolvedValue({
        current: { id: 30, name: 'Paper' },
        eggs: [{ id: 40, name: 'Fabric', description: 'A modding toolchain.' }],
        can_reinstall: true,
    }),
}));

let host: ReturnType<typeof createExtensionTestHost> | undefined;

afterEach(() => {
    cleanup();
    host?.dispose();
    host = undefined;
});

function renderSwitcher(permissions: string[], state: 'running' | 'offline') {
    const server = createTestServer({ uuid: 'a1b2c3d4-0000-4000-8000-000000000001', owner: false, permissions });
    host = createExtensionTestHost({ extensionId: 'egg-changer', prefix: 'egc', server });
    host.queryClient.setQueryData(serverResourcesQueryOptions(server.attributes.uuid).queryKey, {
        object: 'stats',
        attributes: { current_state: state, is_suspended: false, resources: { memory_bytes: 0, cpu_absolute: 0, disk_bytes: 0, network_rx_bytes: 0, network_tx_bytes: 0, uptime: 0 } },
    });

    const data = { server, configuration: {}, isPending: false, canChangeDockerImage: true, setDockerImage: vi.fn(), refresh: vi.fn() } as unknown as StartupFormSlotData;
    render(<EggSwitcher data={data} />, { wrapper: host.Wrapper });
}

test('offers the eggs of the server group', async () => {
    renderSwitcher(['ext.egg-changer.change', 'settings.reinstall'], 'offline');

    expect(await screen.findByText('Paper')).toBeTruthy();
    expect(screen.getByRole('button', { name: 'Change egg' })).toBeTruthy();
    expect(screen.getByText(/Reinstall afterwards/)).toBeTruthy();
});

test('asks to stop a running server first', async () => {
    renderSwitcher(['ext.egg-changer.change'], 'running');

    expect(await screen.findByText('Stop the server to change its egg.')).toBeTruthy();
});

test('stays hidden without the permission', async () => {
    renderSwitcher(['startup.read'], 'offline');

    await new Promise((resolve) => setTimeout(resolve, 50));
    expect(screen.queryByText('Change egg')).toBeNull();
});
